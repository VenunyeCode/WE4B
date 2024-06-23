import { Component, Input, OnInit, inject } from '@angular/core';
import { NewCommentComponent } from '../new-comment/new-comment.component';
import { CommonModule } from '@angular/common';
import { Post } from 'src/app/classes/Post';
import { UserService } from 'src/app/user.service';
import { Utils } from 'src/app/classes/Utils';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-comments',
  standalone: true,
  imports: [NewCommentComponent, CommonModule, RouterModule],
  templateUrl: './comments.component.html',
  styleUrl: './comments.component.css'
})
export class CommentsComponent implements OnInit {
  @Input() comment!: Post;

  authorAvatarUrl!: string;
  authorUsername!: string;
  commentDate!: string;

  userService: UserService = inject(UserService);

  ngOnInit(): void {
    this.commentProcessing();
  }

  commentProcessing() {
    this.commentDate = Utils.relativeDate(this.comment.post_date);
    this.loadAuthorAvatar();
  }

  loadAuthorAvatar(): void {
    this.userService.loadImage(this.comment.author_avatar).subscribe(
      data => {
        this.authorAvatarUrl = URL.createObjectURL(data);
      },
      error => {
        console.error('Error loading image:', error);
      }
    );
  }
}
