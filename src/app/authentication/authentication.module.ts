import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthenticationRoutingModule } from './authentication-routing.module';
import { AuthenticationComponent } from './authentication.component';
import { LoginComponent } from './components/login/login.component';
import { RegisterComponent } from './components/register/register.component';
import { AdminLoginComponent } from './components/admin-login/admin-login.component';
import { FormsModule } from '@angular/forms';
import { AuthenticationService } from '../authentication.service';
import { HttpClientModule } from '@angular/common/http';
import { ToastrModule } from 'ngx-toastr';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { SessionService } from '../session.service';


@NgModule({
  declarations: [
    AuthenticationComponent,
    AdminLoginComponent
  ],
  imports: [
    CommonModule,
    LoginComponent,
    RegisterComponent,
    FormsModule,
    HttpClientModule,
    //BrowserAnimationsModule,
    ToastrModule.forRoot({
      timeOut: 3000,
      positionClass: 'toast-top-right',
      preventDuplicates: true,
    }),
    AuthenticationRoutingModule
  ],
  providers: [
    AuthenticationService,
    SessionService,
    HttpClientModule,
    //ToastrModule,
  ],
})
export class AuthenticationModule { }
