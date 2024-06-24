import { CommonModule } from '@angular/common';
import { Component, inject, Input, OnInit } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { AuthenticationService } from 'src/app/authentication.service';
import { SessionService } from 'src/app/session.service';

@Component({
  selector: 'app-left-side-bar',
  standalone: true,
  imports:[RouterModule, CommonModule],
  templateUrl: './left-side-bar.component.html',
  styleUrls: ['./left-side-bar.component.css']
})
export class LeftSideBarComponent implements OnInit {

  @Input() idUser!:number;

  authenticationService = inject(AuthenticationService);
  sessionService = inject(SessionService)
  router = inject(Router);

  constructor() { }

  ngOnInit(): void {
  }

  logout() {
    console.log('You clicked on logout');
    this.authenticationService.logout(this.idUser).subscribe(
      response => {
        if (response.status == 'success') {
          this.router.navigateByUrl('/authentication/login');
        } else {
          console.log('Logout failed', response.message);
        }
      },
      error => {
        console.error('Login error', error);
      }
    );
  }

}
