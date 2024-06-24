import { CommonModule } from '@angular/common';
import { Component, Input, OnInit, inject } from '@angular/core';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { Post } from 'src/app/classes/Post';
import { UserService } from 'src/app/user.service';
import { PostComponent } from 'src/app/user/components/post/post.component';

@Component({
  selector: 'app-page',
  standalone: true,
  imports: [CommonModule, RouterModule, PostComponent],
  templateUrl: './page.component.html',
  styleUrl: './page.component.css'
})
export class PageComponent implements OnInit {
  //@Input() userId!: number;

  userPosts: Post[] = [];

  route: ActivatedRoute = inject(ActivatedRoute);
  userService: UserService = inject(UserService);

  ngOnInit(): void {
    const userId = parseInt(this.route.snapshot.params['id'], 10);
    this.getUserPosts(userId);
  }

  getUserPosts(userId: number) {
    this.userService.getUserPosts(userId).subscribe
      (
        response => {
          if (response.status == 'success') {
            this.userPosts = response.data;
          } else {
            console.log('Posts loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }

}
