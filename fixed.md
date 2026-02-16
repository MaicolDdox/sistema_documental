No todas las personas que pertenecen a un semilleros, pertenecen a un proyecto de semilleros

Los proyectos salen de semilleros y de grupo de investigación

2 semilleros pueden tener 1 mismo productos

manejo de roles y de permisos como sofia plus

{
    un proyecto puede tener muchos productos de diferentes semilleros, por ende
    se piensa tener una tabla de productos que no tengan relacion con el proyecto, ya que lo mejor es registrar el producto en una tabla individual y luego digamos si un proyecto
    que vienen se semillero saca un producto en el semillero 1, entonces 
    el proyecto estara vinculado con semilleros mediante una tabla pivote que diga (vinculacion de proyecto) donde este digamos el id del proyecto con el id de tipo de vinculacion ya sea grupo de investigacion o proyecto, y ya luego como un producto sale de proyecto entonces seria la misma mecanica, la tabla producto queda libre y la tabla de proyecto como es libre, entonces se crea una tabla pivote para unir los proyectos con los productos

    RELACIONES 
    {
        Grupos de investigacion con Proyectos (M:M)
        Semilleros con Proyectos (M:M)

        -----------------------------

        Proyectos con Productos (M:M)

        El sistema debe crear 1 solo proyecto, 1 solo producto que partan ya sea de semillero o grupo de investigacion y de ahi que se pueda ramificar con diferentes tipos de vinculaciones.

        vinculacion a semilleros o grupo de investigacion
        vinculaciones de productos a diferentes proyectos
        vinculacion de proyectos con distintos productos que parten de distiintos semilleros o grupos de investigacion. 
    }


    
}
