import { Component, OnDestroy, OnInit, inject } from '@angular/core';
import { IUser } from 'src/app/classes/IUser';
import { AuthenticationService } from 'src/app/authentication.service';
import { SessionService } from 'src/app/session.service';
import { User } from 'src/app/classes/User';
import { UserService } from 'src/app/user.service';
import { CommonModule } from '@angular/common';
import { Post } from 'src/app/classes/Post';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { LeftSideBarComponent } from '../layouts/left-side-bar/left-side-bar.component';
import { TopBarComponent } from '../layouts/top-bar/top-bar.component';
import { FooterComponent } from '../layouts/footer/footer.component';
import { RightSideBarComponent } from '../layouts/right-side-bar/right-side-bar.component';
import { interval, Subscription } from 'rxjs';
import { switchMap } from 'rxjs/operators';
import { Utils } from '../classes/Utils';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';


@Component({
  selector: 'app-faq',
  standalone: true,
  imports: [TopBarComponent, RouterModule, LeftSideBarComponent, FooterComponent],
  templateUrl: './faq.component.html',
  styleUrl: './faq.component.css'
})
export class FaqComponent implements OnInit, OnDestroy{
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
    role: '',
    self_intro: '',
    address: ''
  };

  unreadNotif:number = 0;
  userId!:number;

  sessionService = inject(SessionService)
  userService: UserService = inject(UserService);
  router = inject(Router);
  route: ActivatedRoute = inject(ActivatedRoute);
  sanitizer: DomSanitizer = inject(DomSanitizer);

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
            console.log('Notification loaded successfully = ', response.unread_notif);
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
