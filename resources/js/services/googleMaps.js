/**
 * Frontend Google Maps service — loads the Maps JavaScript SDK once
 * (singleton promise, safe to call from multiple components) and provides
 * a small helper for rendering a map with a single marker.
 *
 * Requires VITE_GOOGLE_MAPS_API_KEY in .env. This key is meant to be
 * public and should be restricted by HTTP referrer in Google Cloud Console.
 */

const CALLBACK_NAME = '__googleMapsServiceCallback';

let loadPromise = null;

export function isGoogleMapsConfigured() {
    return Boolean(import.meta.env.VITE_GOOGLE_MAPS_API_KEY);
}

export function loadGoogleMaps() {
    if (loadPromise) {
        return loadPromise;
    }

    if (window.google?.maps) {
        loadPromise = Promise.resolve(window.google.maps);
        return loadPromise;
    }

    const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

    if (!apiKey) {
        return Promise.reject(new Error('VITE_GOOGLE_MAPS_API_KEY is not set.'));
    }

    loadPromise = new Promise((resolve, reject) => {
        window[CALLBACK_NAME] = () => {
            delete window[CALLBACK_NAME];
            resolve(window.google.maps);
        };

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&loading=async&callback=${CALLBACK_NAME}`;
        script.async = true;
        script.onerror = () => {
            loadPromise = null;
            reject(new Error('Failed to load the Google Maps script.'));
        };
        document.head.appendChild(script);
    });

    return loadPromise;
}

/**
 * Render a map centered on { lat, lng } into the given element, with a
 * single marker at that position. Returns { map, marker }.
 */
export async function renderMap(el, { lat, lng }, options = {}) {
    const maps = await loadGoogleMaps();

    const map = new maps.Map(el, {
        center: { lat, lng },
        zoom: options.zoom ?? 13,
        disableDefaultUI: true,
        zoomControl: true,
        streetViewControl: false,
        mapTypeControl: false,
        fullscreenControl: true,
        ...options.mapOptions,
    });

    const marker = new maps.Marker({
        position: { lat, lng },
        map,
        title: options.markerTitle,
    });

    return { map, marker };
}
