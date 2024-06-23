export class Post {
    public id_post: number;
    public content_post: string;
    public post_date: string;
    public likes: number;
    public reposts: string;
    public views: number;
    public removed: number;
    public media_post: string;
    public id_post_comment: number | null;
    public id_post_repost: number | null;
    public id_user: number;
    public author_name: string;
    public author_avatar: string;
    public author_username: string;
  
    constructor(data: any) {
      this.id_post = data.id_post;
      this.content_post = data.content_post;
      this.post_date = data.post_date;
      this.likes = data.likes;
      this.reposts = data.reposts;
      this.views = data.views;
      this.removed = data.removed;
      this.media_post = data.media_post;
      this.id_post_comment = data.id_post_comment;
      this.id_post_repost = data.id_post_repost;
      this.id_user = data.id_user;
      this.author_name = data.author_name;
      this.author_avatar = data.author_avatar;
      this.author_username = data.author_avatar;
    }
  }
  