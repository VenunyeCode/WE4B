import { CommonModule } from '@angular/common';
import { Component, inject, Input, OnInit } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { Notification } from 'src/app/classes/Notification';
import { Post } from 'src/app/classes/Post';
import { Utils } from 'src/app/classes/Utils';
import { UserService } from 'src/app/user.service';
import { PostComponent } from 'src/app/user/components/post/post.component';

@Component({
  selector: 'app-notification',
  standalone: true,
  imports: [CommonModule, PostComponent],
  templateUrl: './notification.component.html',
  styleUrl: './notification.component.css'
})
export class NotificationComponent implements OnInit {

  @Input() notif!: Notification;

  post!: Post;
  senderImageUrl!: string;
  decodedNotifContent!: SafeHtml;
  formattedNotfiDate!: string;

  userService: UserService = inject(UserService);
  sanitizer: DomSanitizer = inject(DomSanitizer)

  constructor() { }

  ngOnInit(): void {
    this.getPostRelated();
    this.loadImageSender();
    this.decodedNotifContent = this.sanitizer.bypassSecurityTrustHtml(this.notif.content_notification);
    this.formattedNotfiDate = Utils.relativeDate(this.notif.notification_date);
    
  }

  getPostRelated() {
    if (this.notif.id_post != null) {
      this.userService.getPostDetail(this.notif.id_post, 0).subscribe
        (
          response => {
            if (response.status == 'success') {
              console.log('Post detail loaded successfully');
              this.post = response.data[0];
              console.log('Post detail =>',this.post);
            } else {
              console.log('Post detail loading failed', response.message);
            }
          },
          error => {
            console.error('API error', error);
          }
        );
    }
  }

  loadImageSender(): void {
    if (this.notif.id_post != null) {
      this.userService.loadImage(<string>this.notif.author_avatar).subscribe(
        data => {
          this.senderImageUrl = URL.createObjectURL(data);
        },
        error => {
          console.error('Error loading image:', error);
        }
      );
    }
  }

}
