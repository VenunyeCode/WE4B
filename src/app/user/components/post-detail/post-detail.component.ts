import { Component, Input, OnDestroy, OnInit, inject } from '@angular/core';
import { CommentsComponent } from '../comments/comments.component';
import { Post } from 'src/app/classes/Post';
import { CommonModule } from '@angular/common';
import { UserService } from 'src/app/user.service';
import { Utils } from 'src/app/classes/Utils';
import { DomSanitizer, SafeUrl, SafeHtml } from '@angular/platform-browser';
import * as validator from 'validator';
import { IUser } from 'src/app/classes/IUser';
import { SessionService } from 'src/app/session.service';
import { NewCommentComponent } from '../new-comment/new-comment.component';
import { Subscription } from 'rxjs';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';

@Component({
  selector: 'app-post-detail',
  standalone: true,
  imports: [CommentsComponent, CommonModule, NewCommentComponent, RouterModule],
  templateUrl: './post-detail.component.html',
  styleUrl: './post-detail.component.css'
})
export class PostDetailComponent implements OnInit, OnDestroy {
  private refreshSubscription!: Subscription;

  user: IUser = {
    id_user: 0,
    username: '',
    email: '',
    firstname: '',
    lastname: '',
    avatar: '',
    banned_temporarly: 0,
    interdiction_date: null,
    role: ''
  };

  post!: Post;
  comments: Post[] = [];

  authorAvatarUrl!: string;
  authorUsername!: string;
  postDate!: string;
  isMediaPostValid: boolean = false;
  safeMediaUrl: SafeUrl | undefined;
  mediaPostUrl!: string;
  formattedPostView: string = "0";
  formattedComment: string = "0";
  isLiked: boolean = false;
  likeCount: number = 0;
  formattedLike: string = "0";
  decodedContent!: SafeHtml;

  userService: UserService = inject(UserService);
  sanitizer: DomSanitizer = inject(DomSanitizer)
  sessionService = inject(SessionService)
  router = inject(Router);
  route: ActivatedRoute = inject(ActivatedRoute);

  constructor() { }

  ngOnInit(): void {
    const postId = parseInt(this.route.snapshot.params['id'], 10);
    this.user = this.sessionService.get("userdata");
    this.getPostDetail(postId);
      

      this.refreshSubscription = this.userService.refresh$.subscribe(() => {
        this.refreshComments();
      });
  }

  getPostDetail(postId: number) {
    this.userService.getPostDetail(postId, this.user.id_user).subscribe
      (
        response => {
          if (response.status == 'success') {
            console.log('Post detail loaded successfully');
            this.post = response.data[0];
            this.postProcessing();
          } else {
            console.log('Post detail loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }

  refreshComments() {
    this.getCommentsOfPost();
  }

  postProcessing() {
    this.loadAuthorAvatar();
    this.authorUsername = Utils.displayUsername(this.post.author_username);
    this.postDate = Utils.relativeDate(this.post.post_date);
    this.decodedContent = this.sanitizer.bypassSecurityTrustHtml(this.post.content_post);
    this.checkMediaPost();
    this.loadMediaPost();
    this.formattedPostView = Utils.formatLargeNumber(this.post.views);
    this.getCommentsOfPost();
    this.postLikeProcess();
  }

  postLikeProcess() {
    this.likeCount = this.post.likes;
    this.formattedLike = Utils.formatLargeNumber(this.likeCount);
    this.checkPostLike();
  }

  checkPostLike() {
    this.userService.checkPostLike(this.post.id_post, this.user.id_user).subscribe(
      response => {
        if (response.status == 'success') {
          this.isLiked = response.liked;
          console.log('Checking successful, liked = ', response.liked);
        } else {

          console.log('Checking failed', response.message);
        }
      },
      error => {
        console.error('API error', error);
      }
    );
  }

  loadAuthorAvatar(): void {
    this.userService.loadImage(this.post.author_avatar).subscribe(
      data => {
        this.authorAvatarUrl = URL.createObjectURL(data);
      },
      error => {
        console.error('Error loading image:', error);
      }
    );
  }

  checkMediaPost() {
    this.isMediaPostValid = validator.isURL(this.post.media_post);
    if (this.isMediaPostValid) {
      this.safeMediaUrl = this.sanitizer.bypassSecurityTrustResourceUrl(this.post.media_post);
    }
  }

  loadMediaPost(): void {
    if (!this.isMediaPostValid) {
      this.userService.loadImage(this.post.media_post).subscribe(
        data => {
          this.mediaPostUrl = URL.createObjectURL(data);
        },
        error => {
          console.error('Error loading image:', error);
        }
      );
    }
  }

  getCommentsOfPost() {
    this.userService.getCommentsByPost(this.post.id_post).subscribe
      (
        response => {
          if (response.status == 'success') {
            console.log('Comment post loaded successfully');
            this.comments = response.data;
            this.formattedComment = Utils.formatLargeNumber(this.comments.length);
          } else {
            console.log('Comment post loading failed', response.message);
          }
        },
        error => {
          console.error('Login error', error);
        }
      );
  }

  toggleLike() {
    this.isLiked = !this.isLiked;
    this.userService.updatePostLike(this.post.id_post, this.user.id_user, this.isLiked).subscribe(
      response => {
        if (response.status == 'success') {
          if (this.isLiked) {
            this.likeCount += 1;
          } else {
            this.likeCount -= 1;
          }
          //this.likeCount += this.isLiked ? 1 : -1;
          console.log("Afficher likeCount = ", this.likeCount);
          console.log('update successful, likes = ', response.likes);
        } else {
          console.log('update failed', response.message);
        }
      },
      error => {
        console.error('API error', error);
      }
    );

  }

  onPostClick(event: any) {
    console.log('Post is click');
    this.router.navigate(['/user/post/', this.post.id_post]);
  }

  ngOnDestroy(): void {
    if (this.refreshSubscription) {
      this.refreshSubscription.unsubscribe();
    }
  }
}
