<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class OppenApiService
{
    private string $baseUrl;
    private string $bearerToken;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.oppen.base_url', ''), '/');
        $this->bearerToken = config('services.oppen.bearer_token', '');
    }

    /**
     * Auto-generar RFC a partir de nombre, apellidos y fecha de nacimiento.
     * Replica la lógica de calcRFC del JS en lazarza_forms_advanced.php
     */
    public static function calcularRFC(string $nombre, string $apellidoPaterno, ?string $apellidoMaterno, string $fechaNacimiento): string
    {
        if (empty($nombre) || empty($apellidoPaterno) || empty($fechaNacimiento)) {
            return '';
        }

        $skip = ['DE', 'LA', 'LAS', 'MC', 'VON', 'DEL', 'LOS', 'Y', 'MAC'];

        // Limpiar: quitar palabras reservadas
        $clean = function (string $s) use ($skip): string {
            $words = preg_split('/\s+/', mb_strtoupper(trim($s)));
            $filtered = array_filter($words, fn($w) => !in_array($w, $skip));
            return implode(' ', $filtered) ?: mb_strtoupper(trim($s));
        };

        // Primera vocal interna de una cadena
        $vowel = function (string $s): string {
            $len = mb_strlen($s);
            for ($i = 1; $i < $len; $i++) {
                $ch = mb_substr($s, $i, 1);
                if (in_array($ch, ['A', 'E', 'I', 'O', 'U'])) {
                    return $ch;
                }
            }
            return 'X';
        };

        $a1 = $clean($apellidoPaterno);
        $a2 = $apellidoMaterno ? $clean($apellidoMaterno) : '';
        $n  = mb_strtoupper(trim($nombre));

        // Primeras 4 letras: AP[0] + vocal_interna(AP) + AM[0] + N[0]
        $r = mb_substr($a1, 0, 1) . $vowel($a1) . ($a2 !== '' ? mb_substr($a2, 0, 1) : 'X');

        // Si el primer nombre es compuesto tipo JOSE/MARIA, usar el segundo nombre
        $parts = preg_split('/\s+/', $n);
        $nameSkip = ['JOSE', 'MARIA', 'MA', 'MA.', 'J', 'J.'];
        if (in_array($parts[0], $nameSkip) && count($parts) > 1) {
            $r .= mb_substr($parts[1], 0, 1);
        } else {
            $r .= mb_substr($parts[0], 0, 1);
        }

        // Fecha: AAMMDD
        $d = new \DateTime($fechaNacimiento);
        $r .= $d->format('y') . $d->format('m') . $d->format('d');

        // Homoclave placeholder: 3 caracteres (2 alfanuméricos + 1 dígito) para cumplir los 13 del SAT
        $r .= 'XX0';

        // Verificar palabras inconvenientes del SAT
        $bad = ['BUEI', 'BUEY', 'CACA', 'COGE', 'CULO', 'FETO', 'GUEY', 'JOTO', 'MEAR', 'MEON', 'PUTA', 'PUTO', 'RATA'];
        $first4 = mb_substr($r, 0, 4);
        if (in_array($first4, $bad)) {
            $r = mb_substr($r, 0, 1) . 'X' . mb_substr($r, 2);
        }

        return $r;
    }

    /**
     * Mapear género del formulario Laravel al valor de PersonSex de la API Oppen.
     * Oppen: 0 = Masculino, 1 = Femenino, null = No especificado
     */
    private function mapearGenero(?string $genero): ?int
    {
        return match ($genero) {
            'masculino' => 0,
            'femenino'  => 1,
            default     => null,
        };
    }

    /**
     * Construir el body para la API del ERP a partir de los datos del registro.
     */
    public function buildCustomerBody(array $data): array
    {
        return [
            'PersonCustomer'           => true,
            'PersonName'               => $data['nombres'],
            'PersonLastName'           => $data['apellido_paterno'],
            'PersonLastName2'          => $data['apellido_materno'] ?? '',
            'PersonBirthDate'          => $data['fecha_nacimiento'],
            'PersonSex'                => $this->mapearGenero($data['genero'] ?? null),
            'TaxRegNr'                 => $data['rfc'],
            'EmailClubLazarza'         => $data['email'],
            'Phone'                    => $data['telefono'],
            'Province'                 => $data['estado'] ?? '',
            'CityName'                 => $data['municipio'] ?? '',
            'DistrictName'             => $data['colonia'] ?? '',
            'TaxRegSAT'               => '612',
            'Address'                  => $data['calle'] ?? 'Sin especificar',
            'ClubLazarzaPromoEmails'   => (bool)($data['promo_email'] ?? false),
            'ClubLazarzaPromoWhatsApp' => (bool)($data['promo_whatsapp'] ?? false),
        ];
    }

    /**
     * Realizar petición HTTP a la API de Oppen.
     */
    public function apiRequest(string $method, string $endpoint, array $query = [], ?array $body = null): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $headers = ['Authorization: Bearer ' . $this->bearerToken];
        $sentJson = null;

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($body !== null) {
                $sentJson = json_encode($body, JSON_UNESCAPED_UNICODE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $sentJson);
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($sentJson);
            }
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $resp   = curl_exec($ch);
        $err    = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false) {
            Log::error('Oppen API cURL error', ['error' => $err, 'url' => $url]);
            return ['error' => 'cURL: ' . $err];
        }

        return [
            'status'     => $status,
            'raw'        => $resp,
            'json'       => json_decode($resp, true),
            '_method'    => $method,
            '_url'       => $url,
            '_sent_body' => $sentJson,
        ];
    }

    /**
     * Crear un cliente en el ERP Oppen.
     * Retorna el Code del cliente creado o null si falló.
     */
    public function crearCliente(array $data): array
    {
        $body = $this->buildCustomerBody($data);

        Log::info('Oppen API: Creando cliente', [
            'email' => $data['email'],
            'rfc'   => $data['rfc'],
        ]);

        $result = $this->apiRequest('POST', 'Customer', [], $body);

        if (isset($result['error'])) {
            Log::error('Oppen API: Error creando cliente', ['error' => $result['error']]);
            return ['success' => false, 'error' => $result['error']];
        }

        $status = $result['status'] ?? 0;
        if ($status >= 200 && $status < 300) {
            $json = $result['json'] ?? [];
            $code = $json['Code'] ?? ($json['data']['Code'] ?? null);

            Log::info('Oppen API: Cliente creado exitosamente', [
                'code'   => $code,
                'status' => $status,
            ]);

            return [
                'success' => true,
                'code'    => $code,
                'data'    => $json,
            ];
        }

        Log::warning('Oppen API: Respuesta no exitosa al crear cliente', [
            'status' => $status,
            'body'   => $result['raw'] ?? '',
        ]);

        return [
            'success' => false,
            'error'   => 'HTTP ' . $status . ': ' . ($result['raw'] ?? 'Sin respuesta'),
            'status'  => $status,
        ];
    }

    /**
     * Buscar un cliente en Oppen por parámetro (TaxRegNr, EmailClubLazarza, Phone).
     */
    public function buscarCliente(string $param, string $value): ?array
    {
        $result = $this->apiRequest('GET', 'Customer', [$param => $value]);

        if (!isset($result['json']) || !is_array($result['json'])) {
            return null;
        }

        $data = $result['json']['data'] ?? $result['json'];
        if (!is_array($data)) {
            return null;
        }

        // Normalizar a array de clientes
        if (isset($data['Code'])) {
            $data = [$data];
        }

        // Filtrar solo clientes activos
        $data = array_values(array_filter($data, fn($c) =>
            !empty($c['Code']) && (!isset($c['Closed']) || $c['Closed'] != 1)
        ));

        return !empty($data) ? $data : null;
    }

    /**
     * Verificar si un cliente ya existe en Oppen buscando por email, teléfono o RFC.
     */
    public function verificarClienteExistente(string $email, string $telefono, ?string $rfc): ?array
    {
        // Buscar por email
        $result = $this->buscarCliente('EmailClubLazarza', $email);
        if ($result) {
            return ['existe' => true, 'code' => $result[0]['Code'], 'por' => 'email', 'data' => $result[0]];
        }

        // Buscar por teléfono
        $result = $this->buscarCliente('Phone', $telefono);
        if ($result) {
            return ['existe' => true, 'code' => $result[0]['Code'], 'por' => 'telefono', 'data' => $result[0]];
        }

        // Buscar por RFC si se proporcionó
        if ($rfc) {
            $result = $this->buscarCliente('TaxRegNr', $rfc);
            if ($result) {
                return ['existe' => true, 'code' => $result[0]['Code'], 'por' => 'rfc', 'data' => $result[0]];
            }
        }

        return null;
    }

    /**
     * Actualizar un cliente existente en el ERP Oppen.
     * @param string $customerCode - Código del cliente en Oppen
     * @param array $data - Datos del cliente a actualizar
     */
    public function actualizarCliente(string $customerCode, array $data): array
    {
        $body = $this->buildCustomerBody($data);

        Log::info('Oppen API: Actualizando cliente', [
            'code' => $customerCode,
            'email' => $data['email'] ?? null,
        ]);

        $result = $this->apiRequest('PUT', "Customer/{$customerCode}", [], $body);

        if (isset($result['error'])) {
            Log::error('Oppen API: Error actualizando cliente', [
                'code' => $customerCode,
                'error' => $result['error']
            ]);
            return ['success' => false, 'error' => $result['error']];
        }

        $status = $result['status'] ?? 0;
        if ($status >= 200 && $status < 300) {
            $json = $result['json'] ?? [];

            Log::info('Oppen API: Cliente actualizado exitosamente', [
                'code' => $customerCode,
                'status' => $status,
            ]);

            return [
                'success' => true,
                'code' => $customerCode,
                'data' => $json,
            ];
        }

        Log::warning('Oppen API: Respuesta no exitosa al actualizar cliente', [
            'code' => $customerCode,
            'status' => $status,
            'body' => $result['raw'] ?? '',
        ]);

        return [
            'success' => false,
            'error' => 'HTTP ' . $status . ': ' . ($result['raw'] ?? 'Sin respuesta'),
            'status' => $status,
        ];
    }

    /**
     * Obtener información de una sucursal (Office) desde Oppen por código.
     * @param string $code - Código de la sucursal (ej: LZ0125)
     * @return array|null - Datos de la sucursal o null si no existe/está cerrada
     */
    public function obtenerSucursal(string $code): ?array
    {
        $result = $this->apiRequest('GET', "Office/{$code}");

        if (isset($result['error'])) {
            return null;
        }

        $status = $result['status'] ?? 0;
        if ($status === 404) {
            return null; // Sucursal no existe
        }

        if ($status >= 200 && $status < 300) {
            $json = $result['json'] ?? null;
            
            // Verificar que tenga los campos necesarios y no esté cerrada
            if ($json && isset($json['Code']) && isset($json['Name']) && !($json['Closed'] ?? false)) {
                return $json;
            }
        }

        return null;
    }

    // ========== PROMOCIONES ==========

    /**
     * Obtener todas las promociones desde Oppen.
     * La API devuelve un array de objetos PromotionRecord.
     */
    public function obtenerPromociones(): array
    {
        $result = $this->apiRequest('GET', 'PromotionRecord');

        if (isset($result['error'])) {
            Log::error('Oppen API: error al obtener promociones', ['error' => $result['error']]);
            return [];
        }

        $status = $result['status'] ?? 0;
        if ($status < 200 || $status >= 300) {
            Log::warning('Oppen API: respuesta no exitosa al obtener promociones', ['status' => $status]);
            return [];
        }

        $json = $result['json'] ?? [];

        // La API devuelve { data: [...], records_returned, has_more }
        if (isset($json['data']) && is_array($json['data'])) {
            return $json['data'];
        }

        // Si devuelve un array directo
        if (isset($json[0])) {
            return $json;
        }

        // Si devuelve un solo registro (no array), envolverlo
        if (isset($json['Code'])) {
            return [$json];
        }

        return [];
    }

    /**
     * Obtener una promoción específica por código desde Oppen.
     */
    public function obtenerPromocion(string $code): ?array
    {
        $result = $this->apiRequest('GET', "PromotionRecord/{$code}");

        if (isset($result['error'])) {
            return null;
        }

        $status = $result['status'] ?? 0;
        if ($status >= 200 && $status < 300) {
            return $result['json'] ?? null;
        }

        return null;
    }
}

