import { Component, OnInit, OnDestroy, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Subject, from } from 'rxjs';
import { User } from 'src/app/classes/User';
import { UserService } from 'src/app/user.service';
import { faWarning } from '@fortawesome/free-solid-svg-icons';
import { SessionService } from 'src/app/session.service';
import { ToastrService } from 'ngx-toastr';


export interface DataTablesResponse {
  data: any[];
  draw: number;
  recordsFiltered: number;
  recordsTotal: number;
}


@Component({
  selector: 'app-admin-profile',
  templateUrl: './admin-profile.component.html',
  styleUrls: ['./admin-profile.component.css'], 
})
export class AdminProfileComponent implements OnInit, OnDestroy {
  dtTrigger: Subject<any> = new Subject();
  users: User[] = [];
  icon = faWarning;
  session = inject(SessionService);
  toastr = inject(ToastrService);
  user = inject(UserService)

  constructor() {
    this.user.get_users().subscribe(
      data => {
        this.users = data
        console.log(data)
      }
    );

  }

  ngOnInit(): void {
   
  }

  ngOnDestroy(): void {
    this.dtTrigger.unsubscribe();
  }

  warn_user(username : string) : void {
    console.log('Bouton avertir utilisateur clické',username);
    this.user.warn_user(username).subscribe(
      response => {
        if(response.status == "failed"){
          this.toastr.error("Une erreur s'est produite", "Erreur");
        }
        else {
          this.toastr.success("Utilisateur averti avec succès", "Succès");
        }
      }
    );
  }
}
