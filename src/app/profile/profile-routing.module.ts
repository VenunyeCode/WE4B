import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ProfileComponent } from './profile.component';
import { PageComponent } from './page/page.component';
import { NotificationsComponent } from './notifications/notifications.component';
import { EditComponent } from './edit/edit.component';
import { FollowersComponent } from './followers/followers.component';
import { InsightsComponent } from './insights/insights.component';

const routes: Routes = [
  {
    path:'',
    component: ProfileComponent,
    children:[
      {
        path: 'page/:id',
        component: PageComponent,
        pathMatch: 'full'
      },
      {
        path: 'notifications/:id',
        component: NotificationsComponent,
        pathMatch: 'full'
      },
      {
        path: 'edit/:id',
        component: EditComponent,
        pathMatch: 'full'
      },
      {
        path: 'followers/:id',
        component: FollowersComponent,
        pathMatch: 'full'
      },
      {
        path: 'insights/:id',
        component: InsightsComponent,
        pathMatch: 'full'
      },
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ProfileRoutingModule { }
