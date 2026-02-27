declare global {
  interface Window {
    API_URL?: string;
  }
}

export const environment = {
  production: true,
  apiUrl: window.API_URL && window.API_URL !== '__API_URL_PLACEHOLDER__' ? window.API_URL : 'http://localhost/api',
  backendUrl: (window.API_URL && window.API_URL !== '__API_URL_PLACEHOLDER__' ? window.API_URL : 'http://localhost').replace(/\/api$/, '')
};
