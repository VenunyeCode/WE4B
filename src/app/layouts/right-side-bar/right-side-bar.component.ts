import { Component, Input, OnInit, inject } from '@angular/core';
import { IUser } from 'src/app/classes/IUser';
import { AuthenticationService } from 'src/app/authentication.service';
import { SessionService } from 'src/app/session.service';
import { User } from 'src/app/classes/User';
import { UserService } from 'src/app/user.service';
import { CommonModule } from '@angular/common';
import { Post } from 'src/app/classes/Post';
import { RouterModule } from '@angular/router';


@Component({
  selector: 'app-right-side-bar',
  standalone: true,
  imports: [RightSideBarComponent, CommonModule, RouterModule],
  templateUrl: './right-side-bar.component.html',
  styleUrls: ['./right-side-bar.component.css']
})
export class RightSideBarComponent implements OnInit {
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

  @Input() unreadNotif!: number;
  allLikes!: number;
  allViews!: number;
  weekLikes!: number;
  weekViews!: number;
  likeUsers: Post[] = [];
  viewUsers: Post[] = [];
  imageUrl: string = '';

  userService: UserService = inject(UserService);
  authenticationService = inject(AuthenticationService);
  sessionService = inject(SessionService)

  constructor() {

  }

  ngOnInit(): void {
    this.user = this.sessionService.get("userdata");
    this.loadImage();
    this.getInsight();
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

  getInsight() {
    this.userService.getInsight(this.user.id_user).subscribe
      (
        response => {
          if (response.status == 'success') {
            console.log('Insight loaded successfully');
            this.unreadNotif = response.unread_notif;
            this.allLikes = response.likes;
            this.allViews = response.views;
            this.weekLikes = response.week_likes;
            this.weekViews = response.week_views;
            this.likeUsers = response.data_like;
            this.viewUsers = response.data_view;
          } else {
            console.log('Insight loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }

}
