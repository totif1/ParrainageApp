import { Routes } from '@angular/router';
import { HomeComponent } from './components/home/home.component';
import { RegistrationComponent } from './components/registration/registration.component';
import { AdminComponent } from './components/admin/admin.component';
import { AuthGuard } from './guards/auth.guard';

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'inscription', component: RegistrationComponent },
  { path: 'admin', component: AdminComponent },
  { path: 'admin/dashboard', component: AdminComponent, canActivate: [AuthGuard] },
  { path: '**', redirectTo: '' }
];
