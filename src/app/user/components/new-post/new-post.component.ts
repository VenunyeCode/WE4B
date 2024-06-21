import { Component, inject, OnInit } from '@angular/core';
import { IUser } from 'src/app/classes/IUser';
import { AuthenticationService } from 'src/app/authentication.service';
import { SessionService } from 'src/app/session.service';
import { FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { UserService } from 'src/app/user.service';

@Component({
  selector: 'app-new-post',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './new-post.component.html',
  styleUrl: './new-post.component.css'
})
export class NewPostComponent {
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

  imageUrl: string = '';
  imagePreviewUrl: string | ArrayBuffer | null = null;

  postForm: FormGroup;

  userService: UserService = inject(UserService);
  authenticationService = inject(AuthenticationService);
  sessionService = inject(SessionService)

  constructor(private fb: FormBuilder) {
    this.user = this.sessionService.get("userdata");
    this.loadImage();
    this.postForm = this.fb.group({
      content: ['', Validators.required],
      img: [null]
    });
  }

  loadImage() {
    this.authenticationService.loadImage(this.user.avatar).subscribe(
      data => {
        this.imageUrl = URL.createObjectURL(data);
      },
      error => {
        console.error('Error loading image:', error);
      }
    );
  }

  onSubmit() {
    const content = this.postForm.get('content')?.value;
    const userId = this.user.id_user;
    const file = this.postForm.get('img')?.value;
    this.userService.savePost(content, userId, file).subscribe(
      response => {
        this.postForm.reset();
        this.imagePreviewUrl = null;
        this.userService.triggerRefresh();
        console.log('Post saved successfully:', response);
      },
      error => {
        console.error('Error saving post:', error);
      }
    );
  }

  onFileChange(event: any) {
    const file = event.target.files[0];
    if (file) {
      console.log('File is not empty');
      this.postForm.patchValue({
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
