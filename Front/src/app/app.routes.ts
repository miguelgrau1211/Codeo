import { Routes } from '@angular/router';
import { Registro } from './registro/registro';
import { LogrosComponent } from './logros/logros';
import { LandingPageComponent } from './landing-page/landing-page';
import { LoginComponent } from './login/login';
import { DashboardComponent } from './dashboard/dashboard';

export const routes: Routes = [
    { path: '', component: LandingPageComponent },
    { path: 'login', component: LoginComponent },
    { path: 'registro', component: Registro },
    { path: 'logros', component: LogrosComponent },
    { path: 'dashboard', component: DashboardComponent },
];
