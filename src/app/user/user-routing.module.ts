import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { UserComponent } from './user.component';
import { LoginComponent } from '../authentication/components/login/login.component';
import { NewsFeedComponent } from './components/news-feed/news-feed.component';
import { NewPostComponent } from './components/new-post/new-post.component';
import { PostDetailComponent } from './components/post-detail/post-detail.component';

const routes: Routes = [
  {
    path: '',
    component: UserComponent,
    children: [
      {
        path: 'news',
        component: NewsFeedComponent,
        pathMatch: 'full'
      },
      {
        path: 'post/:id',
        component: PostDetailComponent,
        pathMatch: 'full',
        title: 'Post details'
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class UserRoutingModule { }
