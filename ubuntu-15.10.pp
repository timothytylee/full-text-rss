# Puppet file intended to install server componenets for self-hosted FiveFilters.org web services
# This file is intended for base images of:
# Ubuntu 15.10

# Please see here for more information on how to use this: 
# http://help.fivefilters.org/customer/en/portal/articles/1143210-hosting

Exec { path => "/bin:/usr/bin:/usr/local/bin" }

stage { 'first': before => Stage['main'] }
stage { 'last': require => Stage['main'] }

class {
	'init': stage => first;
	'final': stage => last;
}

class init {
	exec { "apt-update": 
		command => "apt-get update"
	}
	package { "fail2ban":
		ensure => latest
	}
	package { "unattended-upgrades":
		ensure => latest
	}
	file { "/etc/apt/apt.conf.d/20auto-upgrades":
		ensure => present,
		content => 'APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";',
		require => Package["unattended-upgrades"]
	}
}

# make sure apt-update run before package
Exec["apt-update"] -> Package <| |>

class apache {
	exec { "enable-mod_rewrite":
		require => Package["apache2"],
		before => Service["apache2"],
		#command => "/usr/sbin/a2enmod rewrite",
		command => "sudo a2enmod rewrite",
	}

	file { "/etc/apache2/mods-available/mpm_prefork.conf":
		ensure => present,
		content => "<IfModule mpm_prefork_module>
        StartServers                     5
        MinSpareServers           5
        MaxSpareServers          10
        MaxRequestWorkers         80
        MaxConnectionsPerChild   0
</IfModule>",
		require => Package["apache2"],
		notify => Exec["restart-apache"]
	}

	file { "/etc/apache2/sites-available/fivefilters.conf":
		ensure => present,
		content => "<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog /dev/null combined
        #CustomLog ${APACHE_LOG_DIR}/access.log combined
        
				KeepAliveTimeout 2
				MaxKeepAliveRequests 10
</VirtualHost>",
		require => Package["apache2"],
		before => Exec["enable-fivefilters-apache2"],
		notify => Exec["restart-apache"]
	}

	exec { "enable-fivefilters-apache2":
		require => [Package["apache2"], Service["apache2"]],
		command => "sudo a2dissite 000-default && sudo a2ensite fivefilters"
	}

	exec { "disable-mod_status":
		require => Package["apache2"],
		before => Service["apache2"],
		command => "sudo a2dismod status",
	}

	package { "apache2":
		ensure => latest
	}

	service { "apache2":
		ensure => running,
		require => Package["apache2"]
	}

	exec { "restart-apache":
		command => "sudo service apache2 restart",
		require => Package["apache2"],
		refreshonly => true
	}
	#TODO: Set AllowOverride All in default config to enable .htaccess
}

class php {
	package { "php5": ensure => latest }
	package { "libapache2-mod-php5": ensure => latest }
	package { "php5-cli": ensure => latest }
	package { "php5-tidy": ensure => latest }
	package { "php5-curl": ensure => latest }
	package { "libcurl4-gnutls-dev": ensure => latest }
	package { "libpcre3-dev": ensure => latest }
	package { "make": ensure=>latest }
	package { "php-pear": ensure => latest }
	package { "php5-dev": ensure => latest }
	package { "php5-intl": ensure => latest }
	package { "php5-gd": ensure => latest }
	package { "php5-imagick": ensure => latest }
	package { "php5-json": ensure => latest }
	#package { "php-http": ensure => latest }
	package { "php5-raphf": ensure => latest }
	package { "php5-propro": ensure => latest }
	file { "/etc/php5/mods-available/fivefilters-php.ini":
		ensure => present,
		content => "engine = On
		expose_php = Off
		max_execution_time = 120
		memory_limit = 128M
		error_reporting = E_ALL & ~E_DEPRECATED
		display_errors = Off
		display_startup_errors = Off
		html_errors = Off
		default_socket_timeout = 120
		file_uploads = Off
		date.timezoe = 'UTC'",
		require => Package["php5"],
		before => Exec["enable-fivefilters-php"],
	}
	exec { "enable-fivefilters-php":
		command => "sudo php5enmod fivefilters-php",
	}	
}

class php_pecl_http {
  # Important: this file needs to be in place before we install the HTTP extension
	file { "/etc/php5/mods-available/http.ini":
		ensure => present,
		#owner => root, group => root, mode => 444,
		content => "; priority=25
extension=raphf.so
extension=propro.so
extension=http.so",
		before => [Exec["install-http-pecl"], Exec["enable-http"]],
		require => Class["php"]
	}

	exec { "enable-http":
		command => "sudo php5enmod http",
		require => Class["php"],
	}
	
	package { "libidn11-dev":
		ensure => latest,
		before => Exec["install-http-pecl"]
	}
	
	package { "libevent-dev":
		ensure => latest,
		before => Exec["install-http-pecl"]
	}

	exec { "install-http-pecl":
		command => "pecl install https://pecl.php.net/get/pecl_http-2.5.5.tgz",
		#command => "sudo pecl install pecl_http",
		# the above is now version 3.0 - requires PHP7
		#command => "pecl install http://pecl.php.net/get/pecl_http-1.7.6.tgz",
		#creates => "/tmp/needed/directory",
		require => Exec["enable-http"]
	}
}

class php_pecl_apcu {
	exec { "install-apcu-pecl":
		command => "sudo pecl install channel://pecl.php.net/APCu-4.0.10",
		#creates => "/tmp/needed/directory",
		require => Class["php"]
	}

	file { "/etc/php5/mods-available/apcu.ini":
		ensure => present,
		#owner => root, group => root, mode => 444,
		content => "extension=apcu.so",
		require => Exec["install-apcu-pecl"],
		before => Exec["enable-apcu"]
	}
	exec { "enable-apcu":
		command => "sudo php5enmod apcu",
		notify => Exec["restart-apache"],
	}
}

class php_cld {
	# see https://github.com/lstrojny/php-cld
	package { "git": ensure => latest }
	
	package { "build-essential": ensure => latest }
	
	file { "/tmp/cld":
		ensure => absent,
		before => Exec["download-cld"],
		recurse => true,
		force => true
	}
	
	exec { "download-cld":
		command => "git clone git://github.com/lstrojny/php-cld.git /tmp/cld",
		require => [Package["git"], Class["php"]],
		before => Exec["build-cld"]
	}
	
	exec { "checkout-cld-version":
		# recent version does not work, so we switch to an older one
		command => "git reset --hard fd5aa5721b01bfe547ff6674fa0daa9c3b791ca3",
		cwd => "/tmp/cld",
		require => Exec["download-cld"],
		before => Exec["build-cld"]
	}
	
	exec { "build-cld":
		command => "./build.sh",
		#new cld:command => "sh compile_libs.sh",
		cwd => "/tmp/cld/vendor/libcld",
		require => Package["build-essential"],
		provider => "shell"
	}
	
	exec { "install-cld-extension":
		command => "phpize && ./configure --with-libcld-dir=/tmp/cld/vendor/libcld && make && sudo make install",
		cwd => "/tmp/cld",
		provider => "shell",
		require => Exec["build-cld"]
	}

	file { "/etc/php5/mods-available/cld.ini":
		ensure => present,
		#owner => root, group => root, mode => 444,
		content => "extension=cld.so",
		require => Exec["install-cld-extension"],
		before => Exec["enable-cld"],
	}

	exec { "enable-cld":
		command => "sudo php5enmod cld",
		notify => Exec["restart-apache"],
	}
}

class final {
	exec { "lower-swappiness":
		command => "echo 'vm.swappiness = 10' >> /etc/sysctl.conf && sudo sysctl -p",
		provider => "shell"
	}
}

include init
include apache
include php
include php_pecl_apcu
include php_cld
include php_pecl_http
include final