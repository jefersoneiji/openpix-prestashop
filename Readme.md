# Woovi

This code is related to the open pix prestashop plugin. 

# WARNING: It's not production ready. The intent of this repo is to test approaches related to the integration. 

### Features
- Can generate a charge
- Is able to display a QR Code related to the charge
- Uses information from cart to generate a charge

### Prerequisites

For this project to run, make sure these software are installed:

- docker 
- visual studio code

and you have admin access to the terminal. 

### Installing

To run locally:
1. In root level... install composer by running each script separately and in this order
   ```cmd
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   ```
   ```cmd
   php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
   ```
   ```cmd
   php composer-setup.php
   ```
   ```cmd
   php -r "unlink('composer-setup.php');"
   ``` 
2. Then install composer dependencies
   ```cmd
   php composer.phar install
   ```
3. Finally spin up docker
   ```cmd
   docker compose up
   ```
    
### To access Store (Front-Office)
  ```http
  http://localhost:8080
  ```
### To acess Admin dashboard (Back-Office)
 ```http
 http://localhost:8080/admin4577
 ```
### Credentials for Admin dashboard

email
```cmd
demo@prestashop.com
```
password
```cmd
prestashop_demo
```

### Credentials for PHP Admin

server
```cmd
some-mysql
```
user
```cmd
root
```
password
```cmd
admin
```
#### PAY ATTENTION TO THIS: The Woovi payment method will only show in cart IF THE ORDER'S DELIVERY COUNTRY AND MODULE'S COUNTRY ARE EQUAL. Pick United States or Brazil.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
