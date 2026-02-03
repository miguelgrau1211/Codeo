import { Routes } from '@angular/router';
import { Registro } from './registro/registro';
import { LogrosComponent } from './logros/logros';
import { LandingPageComponent } from './landing-page/landing-page';
import { LoginComponent } from './login/login';
import { DashboardComponent } from './dashboard/dashboard';
import { Soporte } from './soporte/soporte';
import { ModoHistoria } from './modo-historia/modo-historia';
import { ModoInfinito } from './modo-infinito/modo-infinito';
import { Ranking } from './ranking/ranking';
import { Configuracion } from './configuracion/configuracion';
import { Perfil } from './perfil/perfil';


export const routes: Routes = [
    { path: '', component: LandingPageComponent },
    { path: 'login', component: LoginComponent },
    { path: 'registro', component: Registro },
    { path: 'logros', component: LogrosComponent },
    { path: 'dashboard', component: DashboardComponent },
    { path: 'soporte', component: Soporte },
    { path: 'modo-historia', component: ModoHistoria },
    { path: 'modo-infinito', component: ModoInfinito },
    { path: 'ranking', component: Ranking },
    { path: 'ajustes', component: Configuracion },
    { path: 'perfil', component: Perfil },
    
];
