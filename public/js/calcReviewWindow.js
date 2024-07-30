/**
 * javascript function to calculate review window open and closed
 */
var calcReviewWindow = {
	a: 8, 		// allowed days - user defined
	d: 2, 		// divisor by 2
	i: 10, 		// ignore after - business defined
	s: 5,		// shipped date - month day [1-30], fixed date in time
	m: '',		// max number of allowed days
	
	reviewWindow: function() {
		var o = '';		// review window open
		var c = '';		// review window closed
		var rw = '';	// review window max days
		this.m = this.i/this.d;
		console.log('prop i: ' + this.i + ' prop m ' + this.m);
		
		// calculate the maximum number of possible days to report
		o = this.s + ( (this.a <= this.m) ? this.a : this.m);
		c = this.s + this.i;
		rw = c - o;
		
		var string = 'the review window will open in ' + o + ' days from ship date.' +
		'and close in ' + c + ' days from ship date.';
		
		console.log('the maximum number of days to report on past due deliveries is ' + rw);
	
		return string;
	}
	
};
