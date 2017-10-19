import mysql.connector
from rake_nltk import Rake


# connect to database
cnx = mysql.connector.connect(user='', password='',
                              host='',
                              database='')

# retrieve article titles and descriptions
cursor = cnx.cursor()
cursor.execute("SELECT `ref`,`title`,`description` FROM `articles` ORDER BY `ref` ASC")
articles = cursor.fetchall()
#cnx.close()


# extract keywords
r = Rake()
keywords = []

for i in range(len(articles)):

    title_description = articles[i][1] + " " + articles[i][2]
    title_description = title_description.replace('"','');
    title_description = title_description.replace("/","");
    r.extract_keywords_from_text(title_description)
    rake_phrases = r.get_ranked_phrases()
    rake_words = []

    for j in range(min(len(rake_phrases), 3)):
    
        split_rake_phrase = rake_phrases[j].split()
        
        for k in range(min(len(split_rake_phrase), 3)):
        
            if (split_rake_phrase[k] not in rake_words and len(split_rake_phrase[k]) > 2):
                rake_words.append(split_rake_phrase[k])
                
    keywords.append((articles[i][0], rake_words))


# upload keywords to database
for i in range(len(keywords)):

    keyword_update = ""

    for j in range(len(keywords[i][1])):
            
        keyword_update += "`keyword" + str(j+1) +"` = '" + str(keywords[i][1][j]) + "', "
        
    keyword_update = keyword_update[:len(keyword_update)-2]
        
    cursor.execute("UPDATE `articles` SET " + keyword_update + " WHERE `ref` = " + str(keywords[i][0]))
    
cnx.close()


print("Keywords extracted!")
