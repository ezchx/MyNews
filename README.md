# MyNews

Custom news feed based on user content preferences and collaborative filtering:

Article pre-processing:  
•	Read newsapi.org JSON feed  
•	Store article summary information in an SQL database  
•	Extract article keywords using RAKE  
•	Calculate article bias scores based on subjective word content  

User interface:  
•	Users click on articles  
•	Article keywords are stored in user profiles  
•	Collaborative user profiles are generated using KNN  
•	Article sorting is based on collaborative user profiles
