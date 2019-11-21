#include <iostream>
#include <cctype>
using namespace std;

int main()
{
	char letter;
    cout << "Enter character: ";
	cin >> letter;

    // this implementation doesn't force letters into uppercase
    // and so doesn't work for lowercase letters
//    char up = toupper(letter);

    // if this character is a letter (and not a digit, punctuation, etc)
    if( up >= 'A' && up <= 'Z' ) // or you could use "if( isalpha(up) )"
    {
        if( up == 'A'|| up == 'E'|| up == 'I'|| up == 'O'|| up == 'U' )
            cout << letter << " is a vowel.\n";
        else
            cout << letter << " is a consonant.\n";
    }
    else // not a letter
        cout << letter << " is not a letter.\n";
}

