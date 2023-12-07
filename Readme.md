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
1. Spin up docker from CLI
   ```cmd
   docker compose up
   ```
2. Open the prestashop container's CLI and paste this command
   ```cmd
   cd modules/woovi && sh composer.sh
   ```

### To access Store (Front-Office)
  ```
  http://localhost:8080
  ```
### To acess Admin dashboard (Back-Office)
 ```
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
