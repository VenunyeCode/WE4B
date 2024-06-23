import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ProfileRoutingModule } from './profile-routing.module';
import { ToastrModule } from 'ngx-toastr';
import { AgChartsAngular, AgChartsAngularModule } from 'ag-charts-angular';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';


@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    ProfileRoutingModule,
    AgChartsAngularModule,
    ToastrModule.forRoot({
      timeOut: 3000,
      positionClass: 'toast-top-right',
      preventDuplicates: true,
    }),
    FontAwesomeModule
  ],
  providers:[]
})
export class ProfileModule { }
