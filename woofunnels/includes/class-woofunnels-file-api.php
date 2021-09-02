<?php

/**
 * Including WordPress Filesystem API
 */
include_once ABSPATH . '/wp-admin/includes/file.php';
if ( function_exists( 'WP_Filesystem' ) ) {
	WP_Filesystem();
}

if ( class_exists( 'WP_Filesystem_Direct' ) ) {
	class WooFunnels_File_Api extends WP_Filesystem_Direct {
		private $upload_dir;
		private static $ins = null;
		private $core_dir = 'woofunnels';
		private $component = '';

		public function __construct( $component ) {
			$upload                    = wp_upload_dir();
			$this->upload_dir          = $upload['basedir'];
			$this->woofunnels_core_dir = $this->upload_dir . '/' . $this->core_dir;
			$this->set_component( $component );

			$this->makedirs();
			self::$ins = 1;
		}

		public function set_component( $component ) {
			if ( '' !== $component ) {
				$this->component = $component;
			}
		}

		public function get_component_dir() {
			return $this->woofunnels_core_dir . '/' . $this->component;
		}

		public function touch( $file, $time = 0, $atime = 0 ) {
			$file = $this->file_path( $file );

			return parent::touch( $file, $time, $atime );
		}

		public function file_path( $file ) {
			$file_path = $this->woofunnels_core_dir . '/' . $this->component . '/' . $file;

			return $file_path;
		}

		public function folder_path( $folder_name ) {
			$folder_path = $this->woofunnels_core_dir . '/' . $folder_name . '/';

			return $folder_path;
		}

		public function is_readable( $file ) {
			$file = $this->file_path( $file );

			return parent::is_readable( $file );
		}

		public function is_writable( $file ) {
			$file = $this->file_path( $file );

			return parent::is_writable( $file );
		}

		public function put_contents( $file, $contents, $mode = false ) {
			$file = $this->file_path( $file );

			return parent::put_contents( $file, $contents, $mode );
		}

		public function delete_file( $file, $recursive = false, $type = 'f' ) {
			$file = $this->file_path( $file );

			return parent::delete( $file, $recursive, $type );
		}

		public function delete_all( $folder_name, $recursive = false ) {
			$folder_path = $this->folder_path( $folder_name );

			return parent::rmdir( $folder_path, $recursive );
		}


		/**
		 * Gets details for files in a directory or a specific file.
		 *
		 * @param string $path Path to directory or file.
		 * @param bool $include_hidden Optional. Whether to include details of hidden ("." prefixed) files.
		 *                               Default true.
		 * @param bool $recursive Optional. Whether to recursively include file details in nested directories.
		 *                               Default false.
		 *
		 * @return array|false {
		 *     Array of files. False if unable to list directory contents.
		 *
		 * @type string $name Name of the file or directory.
		 * @type string $perms *nix representation of permissions.
		 * @type int $permsn Octal representation of permissions.
		 * @type string $owner Owner name or ID.
		 * @type int $size Size of file in bytes.
		 * @type int $lastmodunix Last modified unix timestamp.
		 * @type mixed $lastmod Last modified month (3 letter) and day (without leading 0).
		 * @type int $time Last modified time.
		 * @type string $type Type of resource. 'f' for file, 'd' for directory.
		 * @type mixed $files If a directory and $recursive is true, contains another array of files.
		 * }
		 * @since 2.5.0
		 *
		 */
		public function dirlist( $path, $include_hidden = true, $recursive = false ) {
			if ( $this->is_file( $path ) ) {
				$limit_file = basename( $path );
				$path       = dirname( $path );
			} else {
				$limit_file = false;
			}
			if ( ! $this->is_dir( $path ) ) {
				return false;
			}

			$dir = dir( $path );
			if ( ! $dir ) {
				return false;
			}

			$ret = array();

			while ( false !== ( $entry = $dir->read() ) ) {
				$struc         = array();
				$struc['name'] = $entry;

				if ( '.' == $struc['name'] || '..' == $struc['name'] ) {
					continue;
				}

				if ( ! $include_hidden && '.' == $struc['name'][0] ) {
					continue;
				}

				if ( $limit_file && $struc['name'] != $limit_file ) {
					continue;
				}

				$struc['perms']       = $this->gethchmod( $path . '/' . $entry );
				$struc['permsn']      = $this->getnumchmodfromh( $struc['perms'] );
				$struc['number']      = false;
				$struc['owner']       = $this->owner( $path . '/' . $entry );
				$struc['group']       = $this->group( $path . '/' . $entry );
				$struc['size']        = $this->size( $path . '/' . $entry );
				$struc['lastmodunix'] = $this->mtime( $path . '/' . $entry );
				$struc['lastmod']     = gmdate( 'M j', $struc['lastmodunix'] );
				$struc['time']        = gmdate( 'h:i:s', $struc['lastmodunix'] );
				$struc['type']        = $this->is_dir( $path . '/' . $entry ) ? 'd' : 'f';

				if ( 'd' == $struc['type'] ) {
					if ( $recursive ) {
						$struc['files'] = $this->dirlist( $path . '/' . $struc['name'], $include_hidden, $recursive );
					} else {
						$struc['files'] = array();
					}
				}

				$ret[ $struc['name'] ] = $struc;
			}
			$dir->close();
			unset( $dir );

			return $ret;
		}


		public function delete_folder( $folder_path, $recursive = false ) {
			return parent::rmdir( $folder_path, $recursive );
		}

		public function exists( $file ) {
			$file = $this->file_path( $file );

			return parent::exists( $file );
		}

		public function get_contents( $file ) {
			$file = $this->file_path( $file );

			return parent::get_contents( $file );
		}

		public function makedirs() {
			$component = $this->component;

			if ( parent::is_writable( $this->upload_dir ) ) {
				if ( false === $this->is_dir( $this->woofunnels_core_dir ) ) {
					$this->mkdir( $this->woofunnels_core_dir );
					$file_handle = @fopen( trailingslashit( $this->woofunnels_core_dir ) . '/.htaccess', 'w' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
					if ( $file_handle ) {
						fwrite( $file_handle, 'deny from all' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
						fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
					}
				}
				$dir = $this->woofunnels_core_dir . '/' . $component;
				if ( false === $this->is_dir( $dir ) ) {
					$this->mkdir( $dir );
				}
			}
		}
	}
}
