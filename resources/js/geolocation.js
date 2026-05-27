/**
 * Geolocation Service
 * Solicita y guarda la ubicación del usuario
 */

class GeolocationService {
    constructor() {
        this.apiEndpoint = '/api/v1/location';
        this.locationData = null;
    }

    /**
     * Verificar si el navegador soporta geolocalización
     */
    isGeolocationSupported() {
        return 'geolocation' in navigator;
    }

    /**
     * Solicitar permiso de ubicación al usuario
     */
    async requestLocation() {
        if (!this.isGeolocationSupported()) {
            console.warn('Geolocalización no soportada por este navegador');
            return null;
        }

        return new Promise((resolve, reject) => {
            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.locationData = {
                        latitud: position.coords.latitude,
                        longitud: position.coords.longitude,
                        precision: position.coords.accuracy
                    };
                    resolve(this.locationData);
                },
                (error) => {
                    let errorMessage = 'Error desconocido';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Permiso denegado por el usuario';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Información de ubicación no disponible';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Tiempo de espera agotado';
                            break;
                    }
                    console.warn('Error obteniendo ubicación:', errorMessage);
                    reject(error);
                },
                options
            );
        });
    }

    /**
     * Obtener información de la ciudad usando Reverse Geocoding
     * Usa la API de Nominatim (OpenStreetMap) - gratuita
     */
    async reverseGeocode(lat, lon) {
        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1&accept-language=es`;
            
            const response = await fetch(url, {
                headers: {
                    'User-Agent': 'LaZarzaContigoApp/1.0'
                }
            });

            if (!response.ok) {
                throw new Error('Error en reverse geocoding');
            }

            const data = await response.json();
            
            return {
                ciudad: data.address.city || data.address.town || data.address.village || data.address.municipality || '',
                estado: data.address.state || '',
                pais: data.address.country || 'México',
                direccion_completa: data.display_name
            };
        } catch (error) {
            console.error('Error en reverse geocoding:', error);
            return {
                ciudad: '',
                estado: '',
                pais: 'México'
            };
        }
    }

    /**
     * Guardar la ubicación en el servidor
     */
    async saveLocation(locationData) {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(locationData)
            });

            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Error al guardar ubicación');
            }

            return result;
        } catch (error) {
            console.error('Error guardando ubicación:', error);
            throw error;
        }
    }

    /**
     * Proceso completo: solicitar ubicación, obtener ciudad y guardar
     */
    async captureAndSaveLocation() {
        try {
            // 1. Solicitar ubicación al usuario
            const position = await this.requestLocation();
            
            if (!position) {
                return null;
            }

            console.log('Ubicación obtenida:', position);

            // 2. Obtener información de la ciudad
            const geoInfo = await this.reverseGeocode(position.latitud, position.longitud);
            
            console.log('Información geográfica:', geoInfo);

            // 3. Combinar datos
            const fullLocationData = {
                latitud: position.latitud,
                longitud: position.longitud,
                ciudad: geoInfo.ciudad,
                estado: geoInfo.estado,
                pais: geoInfo.pais
            };

            // 4. Guardar en el servidor
            const result = await this.saveLocation(fullLocationData);
            
            console.log('Ubicación guardada exitosamente:', result);

            return result;
        } catch (error) {
            console.error('Error en el proceso de captura de ubicación:', error);
            return null;
        }
    }

    /**
     * Solicitar ubicación con UI amigable
     */
    async requestWithPrompt(options = {}) {
        const {
            title = '📍 Ubicación',
            message = 'Para brindarte un mejor servicio, ¿nos permites acceder a tu ubicación?',
            showPrompt = true
        } = options;

        // Si el usuario ya otorgó permisos anteriormente, capturar directamente
        if (!showPrompt) {
            return await this.captureAndSaveLocation();
        }

        // Mostrar un prompt amigable antes de solicitar permisos del navegador
        return new Promise((resolve) => {
            const promptShown = window.confirm(`${title}\n\n${message}`);
            
            if (promptShown) {
                this.captureAndSaveLocation().then(resolve);
            } else {
                console.log('Usuario rechazó compartir ubicación');
                resolve(null);
            }
        });
    }
}

// Inicializar el servicio cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si ya se solicitó la ubicación en esta sesión
    const locationRequested = sessionStorage.getItem('locationRequested');
    
    if (!locationRequested) {
        const geoService = new GeolocationService();
        
        // Esperar 2 segundos después de cargar la página para no interrumpir la experiencia
        setTimeout(() => {
            geoService.requestWithPrompt({
                title: '📍 Bienvenido a La Zarza Contigo',
                message: 'Para brindarte una mejor experiencia y mostrarte promociones cercanas, ¿nos permites acceder a tu ubicación?',
                showPrompt: true
            });
            
            // Marcar que ya se solicitó en esta sesión
            sessionStorage.setItem('locationRequested', 'true');
        }, 2000);
    }
});

// Exportar para uso global
window.GeolocationService = GeolocationService;
