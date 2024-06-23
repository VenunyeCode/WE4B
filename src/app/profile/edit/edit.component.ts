import { CommonModule } from '@angular/common';
import { Component, inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { AuthenticationService } from 'src/app/authentication.service';
import { IUser } from 'src/app/classes/IUser';
import { SessionService } from 'src/app/session.service';
import { UserService } from 'src/app/user.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-edit',
  standalone: true,
  imports: [CommonModule, RouterModule, ReactiveFormsModule],
  templateUrl: './edit.component.html',
  styleUrl: './edit.component.css'
})
export class EditComponent {

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

  imagePreviewUrl: string | ArrayBuffer | null = null;

  editForm!: FormGroup;

  toastr = inject(ToastrService);
  userService: UserService = inject(UserService);
  authenticationService = inject(AuthenticationService);
  sessionService = inject(SessionService);

  constructor(private fb: FormBuilder) {
    this.user = this.sessionService.get("userdata");
    this.user.avatar = (this.user.avatar == null || this.user.avatar.length === 0) ? 'uploads/member/no-image-available.png' : this.user.avatar;
    this.initForm();
  }

  onSubmit() {
    const file = this.editForm.get('img')?.value;
    const updateUser: IUser = {
      id_user: this.editForm.get('id_user')?.value,
      username: this.editForm.get('username')?.value,
      email: this.editForm.get('email')?.value,
      firstname: this.editForm.get('firstname')?.value,
      lastname: this.editForm.get('lastname')?.value,
      avatar: 'no-image-available.png',
      banned_temporarly: 0,
      interdiction_date: null,
      role: '',
      password: this.editForm.get('password')?.value,
      address: this.editForm.get('address')?.value,
      phone: this.editForm.get('phone')?.value,
      self_intro: this.editForm.get('self_intro')?.value
    }
    this.userService.updateUserInfo(updateUser, file).subscribe(
      response => {

        this.toastr.success('Information mis à jour avec succès!', 'Success');
        this.sessionService.clear();
        this.sessionService.set('userdata', response.data);
        this.user = this.sessionService.get("userdata");
        //this.initForm();
        window.location.reload();
        console.log('User saved successfully:', response);
      },
      error => {
        this.toastr.error('Erreur lors de la mise à jour!', 'Erreur');
        console.error('Error saving user:', error);
      }
    );
  }

  loadImage() {
    this.authenticationService.loadImage(this.user.avatar).subscribe(
      data => {
        this.imagePreviewUrl = URL.createObjectURL(data);
      },
      error => {
        console.error('Error loading image:', error);
      }
    );
  }

  initForm() {
    this.editForm = this.fb.group({
      id_user: [this.user.id_user],
      firstname: [this.user.firstname, Validators.required],
      lastname: [this.user.lastname, Validators.required],
      email: [this.user.email, [Validators.required, Validators.email]],
      phone: [this.user.phone, Validators.required],
      password: [''],
      address: [this.user.address, Validators.required],
      self_intro: [this.user.self_intro, Validators.required],
      img: [null]
    });
    this.loadImage();
  }

  onFileChange(event: any) {
    const file = event.target.files[0];
    if (file) {
      console.log('File is not empty');
      this.editForm.patchValue({
        img: file
      });

      const reader = new FileReader();
      reader.onload = () => {
        this.imagePreviewUrl = reader.result;
      };
      reader.readAsDataURL(file);
    }
  }
}
