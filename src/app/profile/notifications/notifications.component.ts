import { CommonModule } from '@angular/common';
import { Component, inject, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { NotificationComponent } from '../notification/notification.component';
import { Notification } from 'src/app/classes/Notification';
import { UserService } from 'src/app/user.service';

@Component({
  selector: 'app-notifications',
  standalone: true,
  imports: [CommonModule, RouterModule, NotificationComponent],
  templateUrl: './notifications.component.html',
  styleUrl: './notifications.component.css'
})
export class NotificationsComponent implements OnInit {

  notifications: Notification[] = [];
  userId!:number;

  route: ActivatedRoute = inject(ActivatedRoute);
  userService: UserService = inject(UserService);

  ngOnInit(): void {
    this.userId = parseInt(this.route.snapshot.params['id'], 10);
    this.getUserNotifications();
  }

  getUserNotifications(){
    this.userService.getUserNotifications(this.userId).subscribe
      (
        response => {
          if (response.status == 'success') {
            this.notifications = response.data;
          } else {
            console.log('Notifications loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }
}
