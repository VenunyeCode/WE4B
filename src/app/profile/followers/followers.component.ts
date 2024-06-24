import { CommonModule } from '@angular/common';
import { Component, inject, OnInit } from '@angular/core';
import { RouterModule } from '@angular/router';
import { IUser } from 'src/app/classes/IUser';
import { Utils } from 'src/app/classes/Utils';
import { SessionService } from 'src/app/session.service';
import { UserService } from 'src/app/user.service';

@Component({
  selector: 'app-followers',
  standalone: true,
  imports: [RouterModule, CommonModule],
  templateUrl: './followers.component.html',
  styleUrl: './followers.component.css'
})
export class FollowersComponent implements OnInit {
  userLog: IUser = {
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

  nbLikers: number = 0;
  nbFollowers: number = 0;
  nbFollowings: number = 0;
  likeUsers: IUser[] = [];
  followerUsers: IUser[] = [];
  followingUsers: IUser[] = [];

  userService: UserService = inject(UserService);
  sessionService = inject(SessionService);

  constructor() {

  }

  ngOnInit(): void {
    this.userLog = this.sessionService.get("userdata");
    this.getInsight();
  }

  getInsight() {
    this.userService.getFollowersLikers(this.userLog.id_user).subscribe
      (
        response => {
          if (response.status == 'success') {
            this.nbLikers = response.likes;
            this.nbFollowers = response.followers;
            this.nbFollowings = response.followings;
            this.likeUsers = response.data_like;
            this.followerUsers = response.data_follower;
            this.followingUsers = response.data_following;
            this.processData();
          } else {
            console.log('Follows loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }

  unsubscribe(unsuscribeId: number) {
    this.userService.followUser(unsuscribeId, this.userLog.id_user, false).subscribe(
      response => {
        if (response.status == 'success') {
          window.location.reload();
        } else {
          console.log('update failed', response.message);
        }
      },
      error => {
        console.error('API error', error);
      }
    );
  }

  processData() {
    this.likeUsers.forEach(user => {
      this.userService.loadImage(user.avatar).subscribe(
        data => {
          const reader = new FileReader();
          reader.onloadend = () => {
            user.avatar = reader.result as string;
          };
          reader.readAsDataURL(data);
        },
        error => {
          console.error('Error loading image for user:', user.username, error);
        }
      );
      user.username = Utils.displayUsername(user.username);
    });

    this.followerUsers.forEach(user => {
      this.userService.loadImage(user.avatar).subscribe(
        data => {
          const reader = new FileReader();
          reader.onloadend = () => {
            user.avatar = reader.result as string;
          };
          reader.readAsDataURL(data);
        },
        error => {
          console.error('Error loading image for user:', user.username, error);
        }
      );
      user.username = Utils.displayUsername(user.username);
    });
    this.followingUsers.forEach(user => {
      this.userService.loadImage(user.avatar).subscribe(
        data => {
          const reader = new FileReader();
          reader.onloadend = () => {
            user.avatar = reader.result as string;
          };
          reader.readAsDataURL(data);
        },
        error => {
          console.error('Error loading image for user:', user.username, error);
        }
      );
      user.username = Utils.displayUsername(user.username);
    });
  }
}
