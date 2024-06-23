export class Statistics {
    public years: number;
    public months: number;
    public post_count: number;
  
    constructor(data: any) {
      this.years = data.years;
      this.months = data.months;
      this.post_count = data.post_count;
    }
  }