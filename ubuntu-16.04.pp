# Puppet file intended to install server componenets for FiveFilters.org web services.
# This file should only be run once when setting up a new server to run Full-Text RSS.
# See http://help.fivefilters.org/customer/portal/articles/1143210-hosting for more information.
# This file is intended for base images of:
# Ubuntu 16.04

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
	#exec { "configure-unattended-upgrades":
	#	require => Package["unattended-upgrades"],
	#	command => "sudo dpkg-reconfigure unattended-upgrades",
	#}
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
	
	exec { "enable-prefork":
		require => Package["apache2"],
		command => "sudo a2dismod mpm_event && sudo a2enmod mpm_prefork",
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
	package { "php7.0": ensure => latest }
	#package { "php-apc": ensure => latest }
	package { "libapache2-mod-php7.0": ensure => latest }
	package { "php7.0-cli": ensure => latest }
	package { "php7.0-tidy": ensure => latest }
	package { "php7.0-curl": ensure => latest }
	#package { "libcurl4-gnutls-dev": ensure => latest }
	package { "libcurl4-openssl-dev": ensure => latest }
	package { "libpcre3-dev": ensure => latest }
	package { "make": ensure=>latest }
	package { "php-pear": ensure => latest }
	package { "php7.0-dev": ensure => latest }
	package { "php7.0-intl": ensure => latest }
	package { "php7.0-gd": ensure => latest }
	package { "php7.0-mbstring": ensure => latest }
	package { "php-imagick": ensure => latest }
	package { "php7.0-json": ensure => latest }
	#package { "php-http": ensure => latest }
	package { "php-raphf": ensure => latest }
	package { "php-propro": ensure => latest }
	package { "php7.0-zip": ensure => latest }
	# for gumbo-php
	package { "libgumbo1": ensure => latest }
	package { "libgumbo-dev": ensure => latest }
	package { "libxml2": ensure => latest }
	package { "libxml2-dev": ensure => latest }

	file { "/etc/php/7.0/mods-available/fivefilters-php.ini":
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
		require => Package["php7.0"],
		before => Exec["enable-fivefilters-php"],
	}
	exec { "enable-fivefilters-php":
		command => "sudo phpenmod fivefilters-php",
	}	
}

class php_pecl_http {
  # Important: this file needs to be in place before we install the HTTP extension
	file { "/etc/php/7.0/mods-available/http.ini":
		ensure => present,
		#owner => root, group => root, mode => 444,
		content => "; priority=25
;extension=raphf.so
;extension=propro.so
extension=http.so",
		before => [Exec["install-http-pecl"], Exec["enable-http"]],
		require => Class["php"]
	}

	exec { "enable-http":
		command => "sudo phpenmod http",
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
		# For some reason this command doesn't return a success code, even though 
		# it appears to succeed. So we use || /bin/true
		command => "sudo pecl install channel://pecl.php.net/pecl_http-3.1.0.tgz || /bin/true",
		#creates => "/tmp/needed/directory",
		require => Exec["enable-http"]
	}
}

class php_pecl_apcu {
	exec { "install-apcu-pecl":
		command => "sudo pecl install channel://pecl.php.net/APCu-5.1.8",
		#creates => "/tmp/needed/directory",
		require => Class["php"]
	}

	file { "/etc/php/7.0/mods-available/apcu.ini":
		ensure => present,
		#owner => root, group => root, mode => 444,
		content => "extension=apcu.so",
		require => Exec["install-apcu-pecl"],
		before => Exec["enable-apcu"]
	}
	exec { "enable-apcu":
		command => "sudo phpenmod apcu",
		notify => Exec["restart-apache"],
	}
}

class php_gumbo {
	# see https://github.com/layershifter/gumbo-php
	package { "git": ensure => latest }
	package { "build-essential": ensure => latest }
	
	file { "/tmp/gumbo":
		ensure => absent,
		before => Exec["download-gumbo"],
		recurse => true,
		force => true
	}
	
	exec { "download-gumbo":
		command => "git clone git://github.com/layershifter/gumbo-php.git /tmp/gumbo",
		require => [Package["git"], Class["php"]]
	}
	
	exec { "install-gumbo-extension":
		command => "phpize && ./configure && make && sudo make install",
		cwd => "/tmp/gumbo",
		provider => "shell",
		require => Exec["download-gumbo"]
	}

	file { "/etc/php/7.0/mods-available/gumbo.ini":
		ensure => present,
		#owner => root, group => root, mode => 444,
		content => "extension=gumbo.so",
		require => Exec["install-gumbo-extension"],
		before => Exec["enable-gumbo"]
	}

	exec { "enable-gumbo":
		command => "sudo phpenmod gumbo",
		notify => Exec["restart-apache"],
		require => Exec["install-gumbo-extension"]
	}
}

class php_pecl_apc_bc {
	exec { "install-apc-bc-pecl":
		command => "sudo pecl install channel://pecl.php.net/apcu_bc-1.0.3",
		#creates => "/tmp/needed/directory",
		require => Class["php_pecl_apcu"]
	}

	file { "/etc/php/7.0/mods-available/z_apc_bc.ini":
		ensure => present,
		#owner => root, group => root, mode => 444,
		content => "extension=apc.so",
		require => Exec["install-apc-bc-pecl"],
		before => Exec["enable-apc-bc"]
	}
	exec { "enable-apc-bc":
		command => "sudo phpenmod z_apc_bc",
		notify => Exec["restart-apache"],
	}
}

class final {
	exec { "lower-swappiness":
		command => "echo 'vm.swappiness = 10' >> /etc/sysctl.conf && sudo sysctl -p",
		provider => "shell"
	}
	exec { "enable-php":
		command => "sudo a2enmod php7.0 && sudo service apache2 restart",
		provider => "shell"
	}
}

include init
include apache
include php
include php_pecl_apcu
include php_pecl_apc_bc
include php_pecl_http
include php_gumbo
include final