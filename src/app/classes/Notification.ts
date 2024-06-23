export class Notification {
    public id_notification: number;
    public content_notification: string;
    public notification_date: string;
    public is_notification_read: number;
    public id_post: number | null;
    public id_interdiction: number | null;
    public removed: number;
    public post_author: number | null;
    public id_user: number;
    public author_avatar: string | null;
  
    constructor(data: any) {
      this.id_notification = data.id_notification;
      this.content_notification = data.content_notification;
      this.notification_date = data.notification_date;
      this.is_notification_read = data.is_notification_read;
      this.id_post = data.id_post;
      this.id_interdiction = data.id_interdiction;
      this.removed = data.removed;
      this.post_author = data.post_author;
      this.id_user = data.id_user;
      this.author_avatar = data.author_avatar;
    }
  }