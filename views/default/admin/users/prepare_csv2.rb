#!/usr/bin/env ruby
# encoding: UTF-8

require 'csv'

# Load file
csv_fname = 'bd2.csv'
csv_professor = 'professor_out.csv'

# Save `matches` and a copy of the `headers`
matches = nil
headers = nil

# Iterate through the `csv` file and locate where
# data matches the options.
lines = []
CSV.foreach( csv_fname, :col_sep => ";",:quote_char => "\"" ) do |row|
  unless (row[1] == nil) 
    first_name, *lname = row[0].split(/ /) 
    last_name = lname.join(' ')
  end
  
  line = {
    :first_name => first_name,
    :last_name => last_name,
    :email => row[1].to_s.downcase,
  }
  lines << line
end



CSV.open(csv_professor, "wb", {:col_sep => "\t"}) do |csv|
  # csv << [:first_name, :last_name, :email, :mentor, :email_mentor ]
  i=0
  lines.each do |l|
    csv << l.values.to_a 
    i = i+1
  end
end
