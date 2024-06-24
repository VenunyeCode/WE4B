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
  sessionService = inject(SessionService);

  constructor() {

  }

  ngOnInit(): void {
    this.user = this.sessionService.get("userdata");
    this.user.avatar = (this.user.avatar == null || this.user.avatar.length === 0) ? 'uploads/member/no-image-available.png' : this.user.avatar;
    this.loadImage(this.user.avatar);
    this.getInsight();
  }

  loadImage(filename: string) {
    this.authenticationService.loadImage(filename).subscribe(
      data => {
        this.imageUrl = URL.createObjectURL(data);
      },
      error => {
        console.error('Error loading image:', error);
      }
    );
  }

  loadInsightImage(filename: string): string {
    let url = "";
    this.authenticationService.loadImage(filename).subscribe(
      data => {
        url = URL.createObjectURL(data);
      },
      error => {
        console.error('Error loading image:', error);
      }
    );
    return url;
  }

  getInsight() {
    this.userService.getInsight(this.user.id_user).subscribe
      (
        response => {
          if (response.status == 'success') {
            this.unreadNotif = response.unread_notif;
            this.allLikes = response.likes;
            this.allViews = response.views;
            this.weekLikes = response.week_likes;
            this.weekViews = response.week_views;
            this.likeUsers = response.data_like;
            this.viewUsers = response.data_view;

            this.loadUserImages();
          } else {
            console.log('Insight loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }

  loadUserImages() {
    this.viewUsers.forEach(user => {
      this.authenticationService.loadImage(user.author_avatar).subscribe(
        data => {
          const reader = new FileReader();
          reader.onloadend = () => {
            user.author_avatar = reader.result as string;
          };
          reader.readAsDataURL(data);
        },
        error => {
          console.error('Error loading image for user:', user.author_name, error);
        }
      );
    });

    this.likeUsers.forEach(user => {
      this.authenticationService.loadImage(user.author_avatar).subscribe(
        data => {
          const reader = new FileReader();
          reader.onloadend = () => {
            user.author_avatar = reader.result as string;
          };
          reader.readAsDataURL(data);
        },
        error => {
          console.error('Error loading image for user:', user.author_name, error);
        }
      );
    });
  }

}
