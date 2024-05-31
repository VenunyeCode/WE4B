import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { UserModule } from './user/user.module';
import { AuthenticationModule } from './authentication/authentication.module';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { ToastrModule } from 'ngx-toastr';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { RouterModule } from '@angular/router';
import { RightSideBarComponent } from './layouts/right-side-bar/right-side-bar.component';
import { AgChartsAngular, AgChartsAngularModule } from 'ag-charts-angular';
import { FaqComponent } from './faq/faq.component';

@NgModule({
  declarations: [
    AppComponent
  ],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    AppRoutingModule,
    FormsModule,
    HttpClientModule,
    AuthenticationModule,
    UserModule,
    AgChartsAngular,
    AgChartsAngularModule,
    FaqComponent
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
