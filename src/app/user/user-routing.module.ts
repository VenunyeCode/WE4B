import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { UserComponent } from './user.component';
import { LoginComponent } from '../authentication/components/login/login.component';

const routes: Routes = [
  {
    path: '',
    component: UserComponent,
    children: [
      //TODO: ici tu ajoutes justes les autres routes
      // sous la forme
      // {
      //   path: 'test',
      //   component: TestComponent,
      //   pathMatch: 'full'
      // }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class UserRoutingModule { }
