const fs = require('fs');
const path = require('path');

const targetPath = path.join(__dirname, 'src/environments/environment.prod.ts');
const apiUrl = process.env.API_URL || 'http://localhost/api';
// Extraemos el backendUrl quitando '/api' si existe al final
const backendUrl = apiUrl.replace(/\/api$/, '') || 'http://localhost';

const envConfigFile = `export const environment = {
  production: true,
  apiUrl: '${apiUrl}',
  backendUrl: '${backendUrl}'
};
`;

console.log('Generating environment.prod.ts with API_URL:', apiUrl);

fs.writeFile(targetPath, envConfigFile, function (err) {
  if (err) {
    console.log(err);
  }
});
