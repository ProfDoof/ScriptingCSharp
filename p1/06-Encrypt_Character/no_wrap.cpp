#include <iostream>
#include <string>
using namespace std;

char encrypt(char letter, int key)
{
   // Make uppercase
   letter = toupper(letter);

   // Shift letter
   letter += key;

   // Should wrap as needed, but doesn't

   return letter;
}

int main()
{
   char letter;
   int key;

   cout << "This program encrypts a single letter.\n\n";

   cout << "Letter: "; cin >> letter;
   cout << "Key: "; cin >> key;

   cout << endl << "Result: " << encrypt(letter,key) << endl;
  
}
