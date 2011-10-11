## Copyright

    Creative Commons License 3.0 (BY-NC-SA)

    BY: Attribution, NC: NonCommercial, SA: ShareAlike

## Installation

Chmod 777 ./template_c

Create databases and named paste.db3 into ./database directory

    DROP TABLE IF EXISTS "content";
    CREATE TABLE [content] (
        [id] INTEGER PRIMARY KEY AUTOINCREMENT, 
        [content] TEXT, 
        [language] VARCHAR(20), 
        [add_date] INT(10)
    );
