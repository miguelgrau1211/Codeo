import { Routes } from '@angular/router';
import { Registro } from './registro/registro';
import { LogrosComponent } from './logros/logros';

export const routes: Routes = [
    { path: '', redirectTo: '/registro', pathMatch: 'full' },
    { path: 'registro', component: Registro },
    { path: 'logros', component: LogrosComponent },
    // Añadiré más rutas a medida que verifique los nombres de los componentes
];
