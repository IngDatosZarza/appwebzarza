<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Usuario;
use App\Services\OppenApiService;

echo "=== CORRECCIÓN DE DATOS EN OPPEN ===\n\n";

// Buscar usuario
$usuario = Usuario::where('email', 'ingdatos@lazarza.com.mx')->first();

if (!$usuario) {
    echo "❌ Usuario NO encontrado\n";
    exit(1);
}

echo "✓ Usuario encontrado:\n";
echo "  ID: {$usuario->id}\n";
echo "  Nombres: {$usuario->nombres}\n";
echo "  Apellido Paterno: {$usuario->apellido_paterno}\n";
echo "  Apellido Materno: {$usuario->apellido_materno}\n";
echo "  Oppen ID: {$usuario->oppen_customer_id}\n\n";

if (!$usuario->oppen_customer_id) {
    echo "❌ Usuario no tiene código de Oppen\n";
    exit(1);
}

echo "=== ACTUALIZANDO EN OPPEN ===\n";

$oppenService = new OppenApiService();

// Preparar datos correctos
$datosCorrectos = [
    'nombres' => $usuario->nombres,
    'apellido_paterno' => $usuario->apellido_paterno,  // Paredes
    'apellido_materno' => $usuario->apellido_materno,  // Galván
    'email' => $usuario->email,
    'telefono' => $usuario->telefono,
    'fecha_nacimiento' => $usuario->fecha_nacimiento,
    'rfc' => $usuario->rfc,
    'genero' => $usuario->genero,
    'promo_email' => $usuario->promo_email === 't',
    'promo_whatsapp' => $usuario->promo_whatsapp === 't',
    'calle' => 'Sin especificar',
    'estado' => 'PUEBLA',
    'municipio' => 'PUEBLA',
    'colonia' => 'SAN ANTONIO ABAD',
];

echo "Datos a enviar:\n";
echo "  PersonName: {$datosCorrectos['nombres']}\n";
echo "  PersonLastName (Apellido Paterno): {$datosCorrectos['apellido_paterno']}\n";
echo "  PersonLastName2 (Apellido Materno): {$datosCorrectos['apellido_materno']}\n\n";

$resultado = $oppenService->actualizarCliente($usuario->oppen_customer_id, $datosCorrectos);

if ($resultado['success']) {
    echo "✅ Cliente actualizado exitosamente en Oppen\n\n";
    
    // Verificar que se actualizó correctamente
    echo "=== VERIFICANDO ACTUALIZACIÓN ===\n";
    $clienteActualizado = $oppenService->buscarCliente('EmailClubLazarza', $usuario->email);
    
    if ($clienteActualizado) {
        $cliente = $clienteActualizado[0];
        echo "✓ Datos actualizados:\n";
        echo "  PersonName: " . ($cliente['PersonName'] ?? 'N/A') . "\n";
        echo "  PersonLastName: " . ($cliente['PersonLastName'] ?? 'N/A') . "\n";
        echo "  PersonLastName2: " . ($cliente['PersonLastName2'] ?? 'N/A') . "\n";
        
        // Verificar si ya no hay duplicación
        if (isset($cliente['PersonLastName']) && isset($cliente['PersonLastName2'])) {
            if ($cliente['PersonLastName'] === $cliente['PersonLastName2']) {
                echo "  ⚠️  DUPLICACIÓN AÚN PRESENTE\n";
            } else {
                echo "  ✅ Apellidos correctos, sin duplicación\n";
            }
        }
    }
} else {
    echo "❌ Error al actualizar:\n";
    echo "  " . ($resultado['error'] ?? 'Error desconocido') . "\n";
}

echo "\n=== FIN DE LA CORRECCIÓN ===\n";
