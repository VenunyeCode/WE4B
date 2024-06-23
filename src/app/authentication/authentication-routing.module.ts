import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LoginComponent } from './components/login/login.component';
import { RegisterComponent } from './components/register/register.component';
import { AdminLoginComponent } from './components/admin-login/admin-login.component';
import { AdminProfileComponent } from './components/admin-profile/admin-profile.component';

const routes: Routes = [
  {
    path: 'login',
    component: LoginComponent,
    pathMatch: 'full'
  },
  {
    path: 'register',
    component: RegisterComponent,
    pathMatch: 'full'
  },
  {
    path: 'administration/login',
    component: AdminLoginComponent,
    pathMatch: 'full'
  },
  {
    path : 'administration/dashboard',
    component : AdminProfileComponent,
    pathMatch :  'full'
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AuthenticationRoutingModule { }
