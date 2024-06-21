import { Component, inject, OnDestroy, OnInit } from '@angular/core';
import { NewPostComponent } from '../new-post/new-post.component';
import { PostComponent } from '../post/post.component';
import { Post } from 'src/app/classes/Post';
import { UserService } from 'src/app/user.service';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-news-feed',
  standalone: true,
  imports: [CommonModule, NewPostComponent, PostComponent, FormsModule],
  templateUrl: './news-feed.component.html',
  styleUrl: './news-feed.component.css'
})
export class NewsFeedComponent implements OnInit, OnDestroy {
  private refreshSubscription!: Subscription;

  postList: Post[] = [];

  userService: UserService = inject(UserService);

  constructor() {

  }

  ngOnInit(): void {
    this.getAllPost();
    this.refreshSubscription = this.userService.refresh$.subscribe(() => {
      this.getAllPost();
    });
  }

  ngOnDestroy(): void {
    if (this.refreshSubscription) {
      this.refreshSubscription.unsubscribe();
    }
  }

  getAllPost() {
    this.userService.getLastestPosts().subscribe
      (
        response => {
          if (response.status == 'success') {
            console.log('Posts loaded successfully');
            this.postList = response.data;
          } else {
            console.log('Posts loading failed', response.message);
          }
        },
        error => {
          console.error('Login error', error);
        }
      );
  }
}
