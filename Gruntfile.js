/* eslint-env node */
module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );

	grunt.initConfig( {
		banana: conf.MessagesDirs,
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
