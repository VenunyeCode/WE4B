import { Component, OnDestroy, OnInit, inject } from '@angular/core';
import { IUser } from 'src/app/classes/IUser';
import { AuthenticationService } from 'src/app/authentication.service';
import { SessionService } from 'src/app/session.service';
import { User } from 'src/app/classes/User';
import { UserService } from 'src/app/user.service';
import { CommonModule } from '@angular/common';
import { Post } from 'src/app/classes/Post';
import { RouterModule } from '@angular/router';
import { LeftSideBarComponent } from '../layouts/left-side-bar/left-side-bar.component';
import { TopBarComponent } from '../layouts/top-bar/top-bar.component';
import { FooterComponent } from '../layouts/footer/footer.component';
import { RightSideBarComponent } from '../layouts/right-side-bar/right-side-bar.component';
import { interval, Subscription } from 'rxjs';
import { switchMap } from 'rxjs/operators';

@Component({
  selector: 'app-user',
  standalone: true,
  imports: [CommonModule, TopBarComponent, LeftSideBarComponent, RouterModule, FooterComponent, RightSideBarComponent],
  templateUrl: './user.component.html',
  styleUrls: ['./user.component.css']
})
export class UserComponent implements OnInit,OnDestroy {
  private refreshSubscription!: Subscription;

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

  unreadNotif:number = 0;
  userId:number = 0;

  sessionService = inject(SessionService)
  userService: UserService = inject(UserService);

  constructor() { }

  ngOnInit(): void {
    this.user = this.sessionService.get("userdata");
    this.user.avatar = (this.user.avatar == null || this.user.avatar.length === 0) ? 'uploads/member/no-image-available.png' : this.user.avatar;
    this.userId = this.user.id_user;
    this.getUnreadNotifications();
  }

  getUnreadNotifications() {
    this.refreshSubscription = interval(5000).pipe(
      switchMap(() => this.userService.getUnreadNotif(this.user.id_user))
    ).subscribe
      (
        response => {
          if (response.status == 'success') {
            this.unreadNotif = response.unread_notif;
          } else {
            console.log('Notification loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }

  ngOnDestroy(): void {
    if (this.refreshSubscription) {
      this.refreshSubscription.unsubscribe();
    }
  }

}
