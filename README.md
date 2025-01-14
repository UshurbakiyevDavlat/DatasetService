# PHP Test Task

1. You need to upload the project to a public repository in gitlab
2. The test project must be run in docker
3. You need to create a readme page about starting and using the project

Develop a service for working with a dataset

Initial data:
.csv dataset
     'category', // client's favorite category
     'firstname',
     'lastname',
     'email',
     'gender',
     'birthDate'

Without using third party libraries:
Read csv file.

Write the received data to the database.

Display data as a table with pagination (but you can also use a simple json api)

Implement filters by values:
     category
     gender
     Date of Birth
     age
     age range (for example, 25 - 30 years)

Implement data export (in csv) according to the specified filters.

# Requirements
To run this project, you will need the following software installed on your machine:

## Docker
## Docker Compose

# Setup
1. Clone the repository https://gitlab.com/DavlatUshur/datasetservice.git
2. Create a new file named .env from .env.example
3. Run the command `docker-compose up -d`
4. Run the command `docker exec php_test composer install`

# Filters
1. http://localhost/?page=2&gender=male&age_range=10-22&birthdate=20-03-2000

# Flow
1. Первый раз при заходе идет коннект с базой и создание таблиц, а так же инсертинг
2. Если инсерт уже произошел, т.е таблица goods не пустая происходит рендеринг таблицы html с пагинацией
3. Фильтры я описал выше
4. При нажатии на кнопку экспорт, какой датасет на данный момент имеется с фильтром или без будет экспортирован и скачен в csv формате

# Plan

## Read CSV file:
To read the .csv file, I would use PHP's built-in function, fgetcsv, to parse each row of the file and create an associative array containing the data for each row. This function can be used in a loop to read each row of the CSV file.

## Write data to the database:
After reading the CSV file, I would use PHP's PDO library to connect to the database and insert the data from each row into the appropriate table.

## Display data as a table with pagination:
To display the data as a table with pagination, I would create a web page using HTML, CSS, and JavaScript, with a PHP script that retrieves the data from the database and outputs it as an HTML table. I would then use JavaScript to implement pagination functionality, which would allow the user to navigate through the table one page at a time.

## Implement filters by values:
To implement filters by values, I would modify the PHP script that retrieves the data from the database to include a WHERE clause that filters the data based on the user's input. For example, if the user selects a category filter, the script would modify the SQL query to include a WHERE clause that filters the data by category.

## Implement data export:
To implement data export in CSV format, I would modify the PHP script that retrieves the data from the database to output the data in CSV format instead of HTML. I would also include a button on the web page that allows the user to download the data as a CSV file. To export data according to the specified filters, I would modify the WHERE clause in the SQL query to filter the data before outputting it in CSV format.
