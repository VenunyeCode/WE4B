import { Component, inject, OnInit } from '@angular/core';
import { IUser } from 'src/app/classes/IUser';
import { Statistics } from 'src/app/classes/Statistics';
import { SessionService } from 'src/app/session.service';
import { UserService } from 'src/app/user.service';
import { AgChartOptions, AgBarSeriesOptions, AgCharts } from 'ag-charts-community';
import { AgChartsAngular, AgChartsAngularModule } from 'ag-charts-angular';

@Component({
  selector: 'app-insights',
  standalone: true,
  imports: [AgChartsAngularModule, AgChartsAngular],
  templateUrl: './insights.component.html',
  styleUrl: './insights.component.css'
})
export class InsightsComponent implements OnInit {

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

  statisticData!: Statistics[];
  options!: AgChartOptions;

  userService: UserService = inject(UserService);
  sessionService = inject(SessionService);

  ngOnInit(): void {
    this.user = this.sessionService.get("userdata");
  
    this.getInsight();
  }

  getInsight() {
    this.userService.getStatistics(this.user.id_user).subscribe
      (
        response => {
          if (response.status == 'success') {
            this.statisticData = response.data;
            this.processData();
          } else {
            console.log('Statistics loading failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
  }

  processData() {
    const processedData = this.statisticData.map(item => ({
      month: new Date(item.years, item.months - 1).toLocaleString('default', { month: 'short' }),
      posts: item.post_count,
    }));
    this.options = {
      autoSize: true,
      /* title: {
        text: 'Nombre de posts par mois',
      }, */
      data: processedData,
      series: [{
        type: 'bar',
        xKey: 'month',
        yKey: 'posts',
        /* fill: '#7cb5ec',
        stroke: '#1f77b4' */
      }]
    };
  }
}
