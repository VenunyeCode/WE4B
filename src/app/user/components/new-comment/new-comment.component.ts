import { CommonModule } from '@angular/common';
import { Component, Input, OnInit, inject } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { UserService } from 'src/app/user.service';

@Component({
  selector: 'app-new-comment',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './new-comment.component.html',
  styleUrl: './new-comment.component.css'
})
export class NewCommentComponent implements OnInit {
  @Input() avatarUrl!: string;
  @Input() idPost!: number;
  @Input() idUser!: number;

  commentForm: FormGroup;

  userService: UserService = inject(UserService);

  constructor(private fb: FormBuilder) {
    this.commentForm = this.fb.group({
      comment: ['', Validators.required]
    });
  }

  ngOnInit(): void {

  }

  onSubmit() {
    if (this.commentForm.valid) {
      const { comment } = this.commentForm.value;

      this.userService.saveComment(this.idPost, this.idUser, comment).subscribe(
        response => {
          if (response.status == 'success') {
            this.commentForm.reset();
            this.userService.triggerRefresh();
            console.log('Number of comments = ', response.comments);
            console.log('Commit successful');
          } else {
            console.log('Commit saved failed', response.message);
          }
        },
        error => {
          console.error('API error', error);
        }
      );
    }
  }
}
