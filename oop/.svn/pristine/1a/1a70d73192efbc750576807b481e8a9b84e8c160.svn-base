#include <cstdlib>
#include <cstdio>
#include <iostream>
#ifdef assert
    #undef assert
#endif
#define assert(x) if (x) ; else (fprintf(stderr,"%s:%d: assert(%s) failed\n",__FILE__,__LINE__,#x),exit(1))

#include <sstream>
#include <cmath>
#include HEADER
#include "subtests.cpp"

#if WIN32
    #define WIN32_LEAN_AND_MEAN 1
    #include <windows.h>
#endif
int main(int argc,char *argv[])
{
#if WIN32
    DWORD dwMode = SetErrorMode(SEM_NOGPFAULTERRORBOX);
    SetErrorMode(dwMode | SEM_NOGPFAULTERRORBOX);
#endif

    if (argc != 2)
        fprintf(stderr,"must have exactly one argument (not %d)\n",argc);
    else {
        int i = atoi(argv[1]);
        if (i == 0)
            fprintf(stderr,"argument must be positive integer\n");
        else
            tests[i-1]();
    }
}
