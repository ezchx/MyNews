# import libraries
import mysql.connector
import pandas as pd
import re


# connect to database
cnx = mysql.connector.connect(user='', password='',
                              host='',
                              database='')
cursor = cnx.cursor()


# retrieve article titles and descriptions
articles = pd.read_sql('SELECT `ref`,`title`,`description` FROM `articles` ORDER BY `ref` ASC', con=cnx)


# retrieve subjective word library
subjective_words = pd.read_csv('/home/ezchecks/public_html/mynews/subjective_words.csv')


# calculate subjectivity ratings and upload to database
all_ratings = []
for i in range(len(articles)):
    
    rating = 0
    title_description = articles.title[i] + " " + articles.description[i]
    
    # count !'s and ?'s
    rating += title_description.count('!')
    rating += title_description.count('?')
    
    # remove special characters
    title_description = re.sub('[^a-zA-Z0-9 \n\.]', '', title_description)
    
    # lowercase
    title_description = title_description.lower()
    
    # split into words
    title_description_list = title_description.split()
    
    # compare to subjective word list
    matches = list(set(title_description_list) & set(subjective_words.subjective_words))
    rating += len(matches)
    #print(matches, rating)
    
    cursor.execute("UPDATE `articles` SET `rating` = " + str(rating) + " WHERE `ref` = " + str(articles.ref[i]))

   
cnx.close()


print("Ratings calculated!")
