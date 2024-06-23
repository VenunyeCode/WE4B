import { Component, OnInit, inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthenticationService } from 'src/app/authentication.service';
import { ToastrService } from 'ngx-toastr';
import { SessionService } from 'src/app/session.service';
import { User } from 'src/app/classes/User';

@Component({
  selector: 'app-admin-login',
  templateUrl: './admin-login.component.html',
  styleUrls: ['./admin-login.component.css']
})
export class AdminLoginComponent implements OnInit {

  authForm: FormGroup;
  router = inject(Router);
  authenticationService = inject(AuthenticationService);
  toastr = inject(ToastrService);
  sessionService = inject(SessionService);

  constructor(private fb : FormBuilder) {
    this.authForm = this.fb.group({
      username: ['', Validators.required],
      password: ['', Validators.required]
    });
   }

  ngOnInit(): void {
  }

  onSubmit(): void {
    if (this.authForm.valid) {
      const { username, password } = this.authForm.value
      const user = new User(username, password)

      this.authenticationService.login_admin(user).subscribe(
        response => {
          if (response.status == 'success') {
            this.toastr.success('Login successful!', 'Success');
            this.sessionService.clear();
            this.sessionService.set('userdata', response.data);
            console.log('Login successful', response.data);
            /* this.router.navigate(['/user', 'news']) */
            this.router.navigateByUrl('/administration/dashboard')
          } else {
            this.toastr.error('Login failed!', 'Error');
            console.log('Login failed', response.message);
          }
        },
        error => {
          console.error('Login error', error);
        }
      );
    }
  }

}
