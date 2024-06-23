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
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, TopBarComponent, LeftSideBarComponent, RouterModule, FooterComponent, RightSideBarComponent],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.css'
})
export class ProfileComponent implements OnInit, OnDestroy {
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

  userProfile: IUser = {
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

  unreadNotif: number = 0;
  followers: number = 0;
  isFollowing: boolean = false;
  authorAvatarUrl!: string;
  authorUsername!: string;
  decodedContent!: SafeHtml;
  followText: string = 'Suivre';
  profileId!:number;
  userId!:number;

  sessionService = inject(SessionService)
  userService: UserService = inject(UserService);
  router = inject(Router);
  route: ActivatedRoute = inject(ActivatedRoute);
  sanitizer: DomSanitizer = inject(DomSanitizer);

  constructor() { }

  ngOnInit(): void {
    const profileId = parseInt(this.route.snapshot.params['id'], 10);
    this.user = this.sessionService.get("userdata");
    this.user.avatar = (this.user.avatar == null || this.user.avatar.length === 0) ? 'uploads/member/no-image-available.png' : this.user.avatar;
    this.userId = this.user.id_user;
    this.getUnreadNotifications();
    this.getUserInfo(profileId);
    
  }

  toggleFollow() {
    this.isFollowing = !this.isFollowing;
    const profileId = parseInt(this.route.snapshot.params['id'], 10);
    this.userService.followUser(profileId, this.user.id_user, this.isFollowing).subscribe(
      response => {
        if (response.status == 'success') {
          /* if (this.isFollowing) {
            this.followers += 1;
          } else {
            this.followers -= 1;
          } */
         this.followers = response.followers;
          //this.followers += this.isFollowing ? 1 : -1;
          this.updateFollowText();
          console.log('update successful, followers = ', response.followers);
        } else {
          console.log('update failed', response.message);
        }
      },
      error => {
        console.error('API error', error);
      }
    );
  }

  updateFollowText() {
    this.followText = this.isFollowing ? 'Ne plus suivre' : 'Suivre';
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

  getUserInfo(profileId: number) {
    this.userService.getUserInfo(profileId, this.user.id_user).subscribe
      (
        response => {
          if (response.status == 'success') {
            console.log('User info loaded successfully');
            this.userProfile = response.data;
            this.userProfile.avatar = (this.userProfile.avatar == null || this.userProfile.avatar.length === 0) ? 'uploads/member/no-image-available.png' : this.userProfile.avatar;
            this.followers = response.followers;
            this.isFollowing = response.is_following;
            this.authorUsername = Utils.displayUsername(this.userProfile.username);
            this.decodedContent = this.sanitizer.bypassSecurityTrustHtml(<string>this.userProfile.self_intro);
            this.updateFollowText();
            this.loadAuthorAvatar();
          } else {
            console.log('User info loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }

  loadAuthorAvatar(): void {
    this.userService.loadImage(this.userProfile.avatar).subscribe(
      data => {
        this.authorAvatarUrl = URL.createObjectURL(data);
      },
      error => {
        console.error('Error loading image:', error);
      }
    );
  }

  checkIfWordInRoute(word: string): boolean {
    const currentRoute = this.router.url;
    return currentRoute.includes(word);
  }

  ngOnDestroy(): void {
    if (this.refreshSubscription) {
      this.refreshSubscription.unsubscribe();
    }
  }
}
