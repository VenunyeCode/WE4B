import { Component, Input, OnInit, inject } from '@angular/core';
import { Router, ActivatedRoute, RouterModule } from '@angular/router';
import { AuthenticationService } from 'src/app/authentication.service';
import { IUser } from 'src/app/classes/IUser';
import { SessionService } from 'src/app/session.service';


@Component({
  selector: 'app-top-bar',
  standalone: true,
  imports: [RouterModule],
  templateUrl: './top-bar.component.html',
  styleUrls: ['./top-bar.component.css']
})
export class TopBarComponent implements OnInit {
  @Input() unreadNotif!: number;

  user: IUser = {
    id_user: 0,
    username: '',
    email: '',
    firstname: '',
    lastname: '',
    avatar: 'no-image-available.png',
    banned_temporarly: 0,
    interdiction_date: null,
    role: ''
  };
  imageUrl: string = '';
  authenticationService = inject(AuthenticationService);
  sessionService = inject(SessionService)
  router = inject(Router);

  constructor() {
    this.user = this.sessionService.get("userdata");
    console.log('Avatar : ', this.sessionService.get("userdata"));
    console.log('User : ', this.user.avatar);
    this.loadImage();
  }

  ngOnInit(): void {
  }

  loadImage() {
    this.authenticationService.loadImage(this.user.avatar).subscribe(
      data => {
        this.imageUrl = URL.createObjectURL(data);
      },
      error => {
        console.error('Error loading image:', error);
      }
    );
  }

  logout() {
    console.log('You clicked on logout');
    this.authenticationService.logout(this.user.id_user).subscribe(
      response => {
        if (response.status == 'success') {

          this.sessionService.set('userdata', response.data)
          console.log('Logout successful', response.data);
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
