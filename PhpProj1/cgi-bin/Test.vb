Imports System
Imports System.Collections.Generic

Public Module Test

    Public Sub Main(args As String())

        Console.WriteLine("Content-Type: text/plain")
        Console.WriteLine()
        Console.WriteLine("HW!")
        Console.WriteLine(DateTime.Now)

        Console.WriteLine()
        Console.WriteLine()

        Dim key As String
        For Each key In Environment.GetEnvironmentVariables().Keys
            Console.WriteLine(key & " = " & Environment.GetEnvironmentVariable(key))
        Next key

        Console.WriteLine()
        Console.WriteLine()

        Console.WriteLine(Console.In.ReadToEnd())

    End Sub

End Module
