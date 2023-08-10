/* eslint-env node */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );

	grunt.initConfig( {
		banana: {
			all: 'i18n/'
		},
		eslint: {
			options: {
				cache: true
			},
			all: '.'
		}
	} );

	grunt.registerTask( 'test', [ 'banana', 'eslint' ] );
	grunt.registerTask( 'default', 'test' );
};
