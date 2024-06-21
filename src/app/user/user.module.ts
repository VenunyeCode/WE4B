import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { UserRoutingModule } from './user-routing.module';
import { UserComponent } from './user.component';
import { TopBarComponent } from '../layouts/top-bar/top-bar.component';
import { FooterComponent } from '../layouts/footer/footer.component';
import { LeftSideBarComponent } from '../layouts/left-side-bar/left-side-bar.component';
import { RightSideBarComponent } from '../layouts/right-side-bar/right-side-bar.component';
import { FormsModule } from '@angular/forms'
import { HttpClientModule } from '@angular/common/http';
import { UserService } from '../user.service';
import { RouterModule } from '@angular/router';



@NgModule({
  declarations: [
    
  ],
  imports: [
    CommonModule,
    UserRoutingModule,
    FormsModule,
    RightSideBarComponent,
    HttpClientModule,
    UserComponent,
    TopBarComponent,
    FooterComponent,
    LeftSideBarComponent,
    RouterModule
  ],
  providers: [
    UserService,
    HttpClientModule,
    //ToastrModule,
  ],
})
export class UserModule { }
