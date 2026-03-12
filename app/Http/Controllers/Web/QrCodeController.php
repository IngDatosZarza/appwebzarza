<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Generar código QR para un cupón usando Google Charts API como fallback
     */
    public function generateCouponQr(Request $request, $codigo_qr)
    {
        try {
            // Verificar si la librería QR está disponible
            if (class_exists('\Endroid\QrCode\QrCode')) {
                return $this->generateWithLibrary($codigo_qr);
            } else {
                // Usar API externa como fallback
                return $this->generateWithGoogleApi($codigo_qr);
            }
        } catch (\Exception $e) {
            // Si todo falla, crear una imagen placeholder
            return $this->generatePlaceholderQr($codigo_qr);
        }
    }
    
    /**
     * Generar QR usando la librería instalada
     */
    private function generateWithLibrary($codigo_qr)
    {
        try {
            // Datos para incluir en el QR
            $qrData = [
                'tipo' => 'cupon_zarza',
                'codigo' => $codigo_qr,
                'timestamp' => time(),
                'sistema' => 'La Zarza Contigo'
            ];
            
            $qrContent = json_encode($qrData);
            
            // Usar la librería QR
            $qrCode = new \Endroid\QrCode\QrCode($qrContent);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);
            
            return response($result->getString())
                ->header('Content-Type', $result->getMimeType())
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (\Exception $e) {
            return $this->generateWithGoogleApi($codigo_qr);
        }
    }
    
    /**
     * Generar QR usando API QR simplificada
     */
    private function generateWithGoogleApi($codigo_qr)
    {
        try {
            // Usar datos más simples para el QR
            $qrContent = "ZARZA|{$codigo_qr}|" . date('Y-m-d');
            $qrContent = urlencode($qrContent);
            
            // URL de API QR gratuita (qr-server.com)
            $size = '300x300';
            $url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data={$qrContent}";
            
            // Obtener la imagen con timeout más corto
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'La Zarza Contigo/1.0'
                ]
            ]);
            
            $imageData = @file_get_contents($url, false, $context);
            
            if ($imageData === false || strlen($imageData) < 100) {
                return $this->generatePlaceholderQr($codigo_qr);
            }
            
            return response($imageData)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (\Exception $e) {
            return $this->generatePlaceholderQr($codigo_qr);
        }
    }
    
    /**
     * Generar imagen placeholder si la librería QR falla
     */
    private function generatePlaceholderQr($codigo_qr)
    {
        // Crear una imagen simple de 300x300 píxeles
        $width = 300;
        $height = 300;
        $image = imagecreate($width, $height);
        
        // Colores
        $bg_color = imagecolorallocate($image, 255, 255, 255); // Blanco
        $text_color = imagecolorallocate($image, 0, 0, 0); // Negro
        $border_color = imagecolorallocate($image, 200, 200, 200); // Gris
        $purple_color = imagecolorallocate($image, 181, 26, 138); // Purple Zarza
        
        // Fondo blanco
        imagefill($image, 0, 0, $bg_color);
        
        // Borde
        imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);
        
        // Título
        $font_size = 5;
        $title = "CUPON La Zarza Contigo";
        $title_width = imagefontwidth($font_size) * strlen($title);
        $title_x = ($width - $title_width) / 2;
        imagestring($image, $font_size, $title_x, 30, $title, $purple_color);
        
        // Simulación de QR (cuadrados)
        $qr_size = 150;
        $qr_x = ($width - $qr_size) / 2;
        $qr_y = 70;
        
        // Fondo del QR
        imagefilledrectangle($image, $qr_x, $qr_y, $qr_x + $qr_size, $qr_y + $qr_size, $text_color);
        
        // Patrón de QR simulado (cuadrados blancos aleatorios)
        for ($i = 0; $i < 20; $i++) {
            $x = $qr_x + rand(5, $qr_size - 15);
            $y = $qr_y + rand(5, $qr_size - 15);
            $size = rand(5, 12);
            imagefilledrectangle($image, $x, $y, $x + $size, $y + $size, $bg_color);
        }
        
        // Código del cupón
        $codigo_text = "Codigo: " . $codigo_qr;
        $code_width = imagefontwidth(3) * strlen($codigo_text);
        $code_x = ($width - $code_width) / 2;
        imagestring($image, 3, $code_x, 240, $codigo_text, $text_color);
        
        // Mensaje
        $msg = "Presentar en sucursal";
        $msg_width = imagefontwidth(2) * strlen($msg);
        $msg_x = ($width - $msg_width) / 2;
        imagestring($image, 2, $msg_x, 270, $msg, $text_color);
        
        // Generar imagen PNG
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($image);
        
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}