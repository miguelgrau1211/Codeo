const fs = require('fs');
const path = require('path');

const apiUrl = process.env.API_URL || 'http://localhost/api';
console.log('--- SETTING ENVIRONMENT VARIABLES ---');
console.log('API_URL detected:', process.env.API_URL ? 'YES' : 'NO (Using default)');
console.log('Final URL:', apiUrl);

// Extraemos el backendUrl quitando '/api' si existe al final
const backendUrl = apiUrl.replace(/\/api$/, '') || 'http://localhost';

const envConfigFile = `export const environment = {
  production: true,
  apiUrl: '${apiUrl}',
  backendUrl: '${backendUrl}'
};
`;

const devConfigFile = `export const environment = {
  production: false,
  apiUrl: '${apiUrl}',
  backendUrl: '${backendUrl}'
};
`;

const prodPath = path.join(__dirname, 'src/environments/environment.prod.ts');
const devPath = path.join(__dirname, 'src/environments/environment.ts');

fs.writeFileSync(prodPath, envConfigFile);
fs.writeFileSync(devPath, devConfigFile);

console.log('Environment files updated successfully!');
console.log('--- END SETTING ENVIRONMENT VARIABLES ---');
