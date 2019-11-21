#include <iostream>
#include <string>
using namespace std;

int convert(char digit)
{
    if( digit >= '0' && digit <= '9' ) // or, isdigit
        return (digit - '0');
    // Not decimal digit, assume 'a'..'f'
    return digit - 'a' + 10;
}

int main()
{
   char a, b;

   cout << "Add two hexadecimal digits and then\n"
        << "show the result as a decimal value.\n\n";

   cout << "Hexadecimal: "; cin >> a;
   cout << "Hexadecimal: "; cin >> b;   

   cout << endl;
   cout << a << " + " << b << " = "
        <<  convert(a)+convert(b) << endl;
  
}
