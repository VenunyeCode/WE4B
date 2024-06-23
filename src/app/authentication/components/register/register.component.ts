import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, ActivatedRoute, RouterModule } from '@angular/router';
import { AuthenticationService } from 'src/app/authentication.service';
import { User } from 'src/app/classes/User';
import { ToastrService } from 'ngx-toastr';
import { SessionService } from 'src/app/session.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, RouterModule, ReactiveFormsModule,],
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {
  registerForm: FormGroup;
  router = inject(Router);
  authenticationService = inject(AuthenticationService);
  toastr = inject(ToastrService)
  sessionService = inject(SessionService)

  constructor(private fb: FormBuilder) {
    this.registerForm = this.fb.group({
      username: ['', Validators.required],
      password: ['', Validators.required],
      confirm: ['', Validators.required],
      phone: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      firstname: ['', Validators.required],
      lastname: ['', Validators.required]
    });
  }

  ngOnInit(): void {
  }

  onSubmit(): void {
    if (this.registerForm.valid) {
      const { username, password, confirm, phone, email, firstname, lastname } = this.registerForm.value;
      if (password != confirm) {
        this.toastr.error('Passwords are not the same', 'Error');
        return;
      }
      const user = new User(username, password, email, firstname, lastname, phone);

      this.authenticationService.register(user).subscribe(
        response => {
          if (response.status == 'success') {
            this.toastr.success('Registred successful!', 'Success');
            this.sessionService.set('userdata', response.data);
            this.registerForm.reset();
            console.log('Registred successful', response.data);
            this.router.navigateByUrl('/user/news')
          } else {
            this.toastr.error('Registred failed!', 'Error');
            console.log('Registred failed', response.message);
          }
        },
        error => {
          console.error('Registred error', error);
        }
      );
    }
  }

}
