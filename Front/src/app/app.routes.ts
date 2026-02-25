import { Routes } from '@angular/router';
import { RegistroComponent } from './components/registro/registro.component';
import { LogrosComponent } from './components/logros/logros.component';
import { LandingPageComponent } from './components/landing-page/landing-page.component';
import { LoginComponent } from './components/login/login.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { SoporteComponent } from './components/soporte/soporte.component';
import { ModoHistoriaComponent } from './components/modo-historia/modo-historia.component';
import { ModoInfinitoComponent } from './components/modo-infinito/modo-infinito.component';
import { RankingComponent } from './components/ranking/ranking.component';
import { ConfiguracionComponent } from './components/configuracion/configuracion.component';
import { PerfilComponent } from './components/perfil/perfil.component';
import { PanelAdminComponent } from './components/panel-admin/panel-admin.component';
import { TiendaTemasComponent } from './components/tienda-temas/tienda-temas.component';

export const routes: Routes = [
  { path: '', component: LandingPageComponent },
  { path: 'login', component: LoginComponent },
  { path: 'registro', component: RegistroComponent },
  { path: 'logros', component: LogrosComponent },
  { path: 'dashboard', component: DashboardComponent },
  { path: 'soporte', component: SoporteComponent },
  { path: 'modo-historia', component: ModoHistoriaComponent },
  { path: 'modo-infinito', component: ModoInfinitoComponent },
  { path: 'ranking', component: RankingComponent },
  { path: 'ajustes', component: ConfiguracionComponent },
  { path: 'perfil', component: PerfilComponent },
  { path: 'panel-admin', component: PanelAdminComponent },
  { path: 'tienda-temas', component: TiendaTemasComponent },
];

