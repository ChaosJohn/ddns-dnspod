# ddns-dnspod
ddns for dnspod.cn which supports both IPv4 and IPv6

## Step 1: 
Clone this project, and modify file `config-sample.ini`(set dnspod-token and access-key) before rename the file to `config.ini`

## Step 2:
Install nginx & php(with fpm and curl modules)

## Step 3:
Run `composer install` to install dependencies, which will create the `vendor` folder. 
If you don't have the `composer` tool, just download it from [vendor.tar.gz](https://github.com/ChaosJohn/ddns-dnspod/releases/download/vendor/vendor.tar.gz). 

## Step 4:
Modify `nginx-sample.conf` and copy to `/path/to/nginx-config-dir/`. (Assume `ddns.your-domain.com` for IPv4 and `ddns6.your-domain.com` for IPv6)

## Step 5:
Restart or reload nginx

# Usage
On you client, just add the line to your crontab: 
`*	*	*	*	*	logfile='/tmp/ddns6.log'; echo "\n$(date)" >> $logfile && curl 'http://ddns6.your-domain.com?key=YOUR-KEY&domain=DOMAIN&sub=SUBDOMAIN' >> $logfile`
