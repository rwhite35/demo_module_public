/**
 * Simple JavaScript Email validation to pre check an email address
 * Additional validations are made on the server side on submit
 */
const validateEmail = {
		
		email: '',
		
		isValid: function()
		{
			return this.checkingEmail( this.email );
		},

		checkingEmail: function( email )
		{
			// console.log( 'validateEmail.checking has email string ' + email );
			var re = /^(?:[a-z0-9!#$%&amp;'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&amp;'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/;
			return re.test( email );
		}
		
}

/**
 * Validates the allow transit days are not greater than half the
 * the business defined ignore after days
 */
const validateAllowDays = {
		iga: '',
		days: '',
		
		isValid: function()
		{
			return this.checkingDays( this.days, this.iga );
		},

		checkingDays: function( y, i ) {
			var stat = false;
			d = i/2;
			if ( y <= d ) stat = true;
			return stat;
		}
}