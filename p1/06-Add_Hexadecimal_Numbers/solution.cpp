#include <iostream>
#include <string>
using namespace std;

int convert16to10(char digit)
{
    if( digit >= '0' && digit <= '9' ) // or, isdigit
        return (digit - '0');
    // Not decimal digit, assume 'a'..'f'
    return digit - 'a' + 10;
}

char convert10to16(int digit)
{
    // if represented by "normal" digit
    if( digit < 10 )
        return '0' + digit;
    // else, need letter representation
    return 'a' + (digit-10);
}

int main()
{
   char a1, a2, b1, b2;
   int  a, b, n;

   cout << "Add two hexadecimal numbers, each two digits,\n"
        << "then show the result as a hexadecimal value.\n\n";

   cout << "First number: "; cin >> a1 >> a2;
   cout << "Second number: "; cin >> b1 >> b2;   

   n = convert16to10(a1)*16 + convert16to10(a2)
     + convert16to10(b1)*16 + convert16to10(b2);

   cout << endl;
   cout << a1 << a2 << " + " << b1 << b2 << " = ";
   if( n > 256 ) // need three digits
   {
      cout << convert10to16(n/256);
      n = n % 256;
   }
   cout << convert10to16(n/16) << convert10to16(n%16) <<endl;  
}

